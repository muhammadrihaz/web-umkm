<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{
    public function __construct(private readonly MidtransGateway $gateway, private readonly InventoryService $inventoryService) {}

    public function initialize(Order $order): Payment
    {
        if ($order->payment_method !== 'midtrans') {
            throw ValidationException::withMessages(['payment' => 'Metode pembayaran pesanan bukan Midtrans.']);
        }

        $payment = DB::transaction(function () use ($order): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $existing = $lockedOrder->payments()->whereIn('status', [PaymentStatus::Initializing, PaymentStatus::Pending, PaymentStatus::Challenge])->latest('attempt_number')->first();
            if ($existing) {
                return $existing;
            }
            $attempt = ((int) $lockedOrder->payments()->max('attempt_number')) + 1;

            return $lockedOrder->payments()->create([
                'attempt_number' => $attempt,
                'provider_order_id' => $lockedOrder->order_number.'-A'.$attempt.'-'.Str::upper(Str::random(4)),
                'status' => PaymentStatus::Initializing,
                'amount' => $lockedOrder->total,
                'currency' => 'IDR',
            ]);
        });

        if ($payment->snap_token) {
            return $payment;
        }

        $result = $this->gateway->createTransaction($order->loadMissing('user', 'items'), $payment);
        $payment->update(['snap_token' => $result['token'], 'redirect_url' => $result['redirect_url'], 'status' => PaymentStatus::Pending]);

        return $payment->refresh();
    }

    /** @param array<string, mixed> $status */
    public function applyVerifiedStatus(Payment $payment, array $status): Payment
    {
        $grossAmount = $this->normalizeIdrAmount((string) ($status['gross_amount'] ?? ''));
        if ($grossAmount !== $payment->amount || (string) ($status['order_id'] ?? '') !== $payment->provider_order_id) {
            throw ValidationException::withMessages(['payment' => 'Identitas atau nominal transaksi tidak cocok.']);
        }
        if (isset($status['currency']) && strtoupper((string) $status['currency']) !== 'IDR') {
            throw ValidationException::withMessages(['payment' => 'Mata uang transaksi tidak cocok.']);
        }
        $configuredMerchant = (string) config('pangkonan.midtrans.merchant_id');
        if ($configuredMerchant !== '' && isset($status['merchant_id']) && ! hash_equals($configuredMerchant, (string) $status['merchant_id'])) {
            throw ValidationException::withMessages(['payment' => 'Merchant transaksi tidak cocok.']);
        }

        $transactionId = isset($status['transaction_id']) ? (string) $status['transaction_id'] : null;
        $mapped = $this->mapStatus((string) ($status['transaction_status'] ?? ''), isset($status['fraud_status']) ? (string) $status['fraud_status'] : null);
        $refundTotal = $this->refundTotal($mapped, $status, $payment->amount);
        $becamePaid = false;
        $excessPayment = false;
        $shouldRelease = false;

        DB::transaction(function () use ($payment, $status, $transactionId, $mapped, $refundTotal, &$becamePaid, &$excessPayment, &$shouldRelease): void {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);
            if (($locked->status === PaymentStatus::Refunded && $mapped !== PaymentStatus::Refunded)
                || ($locked->status === PaymentStatus::PartiallyRefunded && ! in_array($mapped, [PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true))
                || ($locked->status === PaymentStatus::Paid && ! in_array($mapped, [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true))) {
                return;
            }
            $becamePaid = $mapped === PaymentStatus::Paid && $locked->status !== PaymentStatus::Paid;
            $orderAlreadyPaid = in_array($order->payment_status, [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true);
            $excessPayment = $becamePaid && $orderAlreadyPaid;
            $locked->update([
                'status' => $mapped,
                'provider_transaction_id' => $transactionId ?: $locked->provider_transaction_id,
                'provider_payload' => $status,
                'paid_at' => $mapped === PaymentStatus::Paid ? ($locked->paid_at ?? now()) : $locked->paid_at,
            ]);

            if ($mapped === PaymentStatus::Paid) {
                $previous = $order->order_status->value;
                if ($excessPayment) {
                    $order->update(['payment_status' => PaymentStatus::Paid, 'order_status' => OrderStatus::Exception, 'exception_reason' => 'Lebih dari satu attempt pembayaran terverifikasi paid. Periksa kelebihan pembayaran sebelum fulfillment.']);
                    $order->histories()->create(['source' => 'midtrans', 'state_type' => 'payment', 'from_status' => PaymentStatus::Paid->value, 'to_status' => PaymentStatus::Paid->value, 'reason' => 'Kelebihan pembayaran terdeteksi pada attempt lain']);
                    PaymentAdjustment::query()->firstOrCreate(
                        ['provider_adjustment_id' => 'overpayment:'.$locked->id],
                        ['payment_id' => $locked->id, 'type' => 'overpayment', 'amount' => $locked->amount, 'occurred_at' => now(), 'provider_payload' => $status],
                    );
                } else {
                    $order->update(['payment_status' => PaymentStatus::Paid, 'order_status' => OrderStatus::Confirmed, 'paid_at' => $order->paid_at ?? now()]);
                    $order->histories()->create(['source' => 'midtrans', 'state_type' => 'order', 'from_status' => $previous, 'to_status' => OrderStatus::Confirmed->value, 'reason' => 'Pembayaran terverifikasi']);
                }
            } elseif (in_array($mapped, [PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)) {
                $order->update(['payment_status' => $mapped]);
                $this->recordRefundAdjustment($locked, $mapped, (int) $refundTotal, $status);
            } elseif (! in_array($order->payment_status, [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)) {
                $order->update(['payment_status' => $mapped]);
                $shouldRelease = in_array($mapped, [PaymentStatus::Expired, PaymentStatus::Cancelled], true);
            }
        }, 3);

        if ($becamePaid && ! $excessPayment) {
            try {
                $this->inventoryService->commit($payment->order()->firstOrFail());
            } catch (Throwable $exception) {
                $affectedOrder = $payment->order()->firstOrFail();
                $previous = $affectedOrder->order_status->value;
                $affectedOrder->update(['order_status' => OrderStatus::Exception, 'exception_reason' => 'Pembayaran masuk tetapi stok tidak dapat dikomit: '.$exception->getMessage()]);
                $affectedOrder->histories()->create(['source' => 'system', 'state_type' => 'order', 'from_status' => $previous, 'to_status' => OrderStatus::Exception->value, 'reason' => $affectedOrder->exception_reason]);
            }
        }
        if ($shouldRelease) {
            $this->releaseTerminalPayment($payment->order_id, $mapped);
        }

        return $payment->refresh();
    }

    public function validSignature(string $orderId, string $statusCode, string $rawGrossAmount, string $signature): bool
    {
        $expected = hash('sha512', $orderId.$statusCode.$rawGrossAmount.(string) config('pangkonan.midtrans.server_key'));

        return $signature !== '' && hash_equals($expected, $signature);
    }

    private function normalizeIdrAmount(string $amount): int
    {
        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches)) {
            throw ValidationException::withMessages(['payment' => 'Format nominal gateway tidak valid.']);
        }
        $fraction = str_pad($matches[2] ?? '0', 2, '0');
        if ($fraction !== '00') {
            throw ValidationException::withMessages(['payment' => 'Nominal IDR harus berupa rupiah bulat.']);
        }

        return (int) $matches[1];
    }

    private function mapStatus(string $transactionStatus, ?string $fraudStatus): PaymentStatus
    {
        return match ($transactionStatus) {
            'settlement' => $fraudStatus === null || $fraudStatus === 'accept' ? PaymentStatus::Paid : PaymentStatus::Challenge,
            'capture' => $fraudStatus === 'accept' ? PaymentStatus::Paid : PaymentStatus::Challenge,
            'pending' => PaymentStatus::Pending,
            'challenge' => PaymentStatus::Challenge,
            'deny', 'failure' => PaymentStatus::Failed,
            'expire' => PaymentStatus::Expired,
            'cancel' => PaymentStatus::Cancelled,
            'partial_refund' => PaymentStatus::PartiallyRefunded,
            'refund' => PaymentStatus::Refunded,
            default => PaymentStatus::Challenge,
        };
    }

    /** @param array<string, mixed> $status */
    private function refundTotal(PaymentStatus $mapped, array $status, int $paymentAmount): ?int
    {
        if (! in_array($mapped, [PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)) {
            return null;
        }
        if (! isset($status['refund_amount']) && $mapped === PaymentStatus::PartiallyRefunded) {
            throw ValidationException::withMessages(['payment' => 'Nominal partial refund tidak tersedia.']);
        }
        $amount = isset($status['refund_amount']) ? $this->normalizeIdrAmount((string) $status['refund_amount']) : $paymentAmount;
        if ($amount < 1 || $amount > $paymentAmount) {
            throw ValidationException::withMessages(['payment' => 'Nominal refund tidak valid.']);
        }

        return $amount;
    }

    /** @param array<string, mixed> $status */
    private function recordRefundAdjustment(Payment $payment, PaymentStatus $mapped, int $cumulativeAmount, array $status): void
    {
        $recorded = (int) $payment->adjustments()->whereIn('type', ['refund', 'partial_refund'])->sum('amount');
        $delta = $cumulativeAmount - $recorded;
        if ($delta <= 0) {
            return;
        }
        $providerId = isset($status['refund_id'])
            ? (string) $status['refund_id']
            : hash('sha256', $payment->provider_order_id.'|'.$mapped->value.'|'.$cumulativeAmount);
        PaymentAdjustment::query()->firstOrCreate(
            ['provider_adjustment_id' => $providerId],
            ['payment_id' => $payment->id, 'type' => $mapped === PaymentStatus::Refunded ? 'refund' : 'partial_refund', 'amount' => $delta, 'occurred_at' => now(), 'provider_payload' => $status],
        );
    }

    private function releaseTerminalPayment(int $orderId, PaymentStatus $status): void
    {
        DB::transaction(function () use ($orderId, $status): void {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            if (in_array($order->payment_status, [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded], true)) {
                return;
            }
            $this->inventoryService->release($order, 'Pembayaran Midtrans '.$status->value.' terverifikasi');
            if ($order->order_status !== OrderStatus::Cancelled) {
                $previous = $order->order_status->value;
                $order->update(['order_status' => OrderStatus::Cancelled, 'cancelled_at' => $order->cancelled_at ?? now()]);
                $order->histories()->create(['source' => 'midtrans', 'state_type' => 'order', 'from_status' => $previous, 'to_status' => OrderStatus::Cancelled->value, 'reason' => 'Pembayaran '.$status->value.'; reservasi dilepas']);
            }
        }, 3);
    }
}
