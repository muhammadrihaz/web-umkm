<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\MidtransGateway;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=100}';

    protected $description = 'Memproses ulang event durable dan merekonsiliasi payment attempt aktif';

    public function handle(MidtransGateway $gateway, PaymentService $service): int
    {
        PaymentNotification::query()->whereNull('processed_at')->orderBy('id')->limit((int) $this->option('limit'))->get()->each(function (PaymentNotification $notification) use ($gateway, $service): void {
            try {
                $payment = Payment::query()->where('provider_order_id', $notification->provider_order_id)->firstOrFail();
                $service->applyVerifiedStatus($payment, $gateway->status($notification->provider_order_id));
                $notification->update(['processed_at' => now(), 'attempts' => $notification->attempts + 1, 'last_error' => null]);
            } catch (Throwable $exception) {
                $notification->update(['attempts' => $notification->attempts + 1, 'last_error' => mb_substr($exception->getMessage(), 0, 1000)]);
            }
        });

        Payment::query()->whereIn('status', [PaymentStatus::Initializing, PaymentStatus::Pending, PaymentStatus::Challenge])->where('updated_at', '<', now()->subMinutes(2))->orderBy('id')->limit((int) $this->option('limit'))->get()->each(function (Payment $payment) use ($gateway, $service): void {
            try {
                $service->applyVerifiedStatus($payment, $gateway->status($payment->provider_order_id));
            } catch (Throwable) {
                // Snap dapat mengembalikan 404 sebelum pelanggan memilih kanal; status lokal tidak diturunkan.
            }
        });

        return self::SUCCESS;
    }
}
