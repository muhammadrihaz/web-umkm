<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\MidtransGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentController extends Controller
{
    public function initialize(Request $request, Order $order, PaymentService $service): RedirectResponse
    {
        $this->authorize('view', $order);
        $payment = $service->initialize($order);

        return redirect()->route('orders.show', $order)->with('success', 'Sesi pembayaran siap.')->with('snap_token', $payment->snap_token);
    }

    public function webhook(Request $request, PaymentService $service, MidtransGateway $gateway): JsonResponse
    {
        $payload = $request->json()->all();
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'] as $field) {
            if (! isset($payload[$field]) || ! is_scalar($payload[$field])) {
                return response()->json(['message' => 'Payload tidak valid.'], 422);
            }
        }
        $orderId = (string) $payload['order_id'];
        $rawGross = (string) $payload['gross_amount'];
        if (! $service->validSignature($orderId, (string) $payload['status_code'], $rawGross, (string) $payload['signature_key'])) {
            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $payment = Payment::query()->where('provider_order_id', $orderId)->first();
        $eventKey = hash('sha256', implode('|', [$orderId, (string) ($payload['transaction_id'] ?? ''), (string) $payload['transaction_status'], (string) ($payload['fraud_status'] ?? ''), (string) ($payload['status_code'] ?? '')]));
        $notification = PaymentNotification::query()->firstOrCreate(
            ['event_key' => $eventKey],
            ['payment_id' => $payment?->id, 'provider_order_id' => $orderId, 'provider_transaction_id' => $payload['transaction_id'] ?? null, 'transaction_status' => $payload['transaction_status'], 'signature' => $payload['signature_key'], 'payload' => $payload, 'received_at' => now()],
        );
        if ($notification->processed_at) {
            return response()->json(['message' => 'Event sudah diproses.']);
        }
        if (! $payment) {
            $notification->update(['attempts' => $notification->attempts + 1, 'last_error' => 'Payment attempt tidak ditemukan']);

            return response()->json(['message' => 'Payment attempt tidak ditemukan.'], 404);
        }

        try {
            $verified = $gateway->status($orderId);
            if (isset($payload['transaction_id'], $verified['transaction_id']) && (string) $payload['transaction_id'] !== (string) $verified['transaction_id']) {
                throw ValidationException::withMessages(['transaction_id' => 'Transaction ID tidak cocok.']);
            }
            $service->applyVerifiedStatus($payment, $verified);
            $notification->update(['processed_at' => now(), 'attempts' => $notification->attempts + 1, 'last_error' => null]);

            return response()->json(['message' => 'OK']);
        } catch (Throwable $exception) {
            $notification->update(['attempts' => $notification->attempts + 1, 'last_error' => mb_substr($exception->getMessage(), 0, 1000)]);
            Log::warning('Midtrans webhook belum diproses', ['notification_id' => $notification->id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Event tersimpan dan akan dicoba ulang.'], 503);
        }
    }
}
