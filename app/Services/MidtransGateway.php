<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransGateway
{
    public function __construct()
    {
        Config::$serverKey = (string) config('pangkonan.midtrans.server_key');
        Config::$isProduction = (bool) config('pangkonan.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /** @return array{token: string, redirect_url: string} */
    public function createTransaction(Order $order, Payment $payment): array
    {
        $payload = [
            'transaction_details' => ['order_id' => $payment->provider_order_id, 'gross_amount' => $order->total],
            'customer_details' => ['first_name' => $order->recipient_name, 'email' => $order->user->email, 'phone' => $order->recipient_phone],
            'item_details' => $order->items->map(fn ($item): array => ['id' => $item->sku, 'price' => $item->unit_price, 'quantity' => $item->quantity, 'name' => mb_substr($item->product_name, 0, 50)])->when($order->delivery_fee > 0, fn ($items) => $items->push(['id' => 'DELIVERY', 'price' => $order->delivery_fee, 'quantity' => 1, 'name' => 'Ongkir lokal']))->values()->all(),
        ];
        $response = Snap::createTransaction($payload);

        return ['token' => $response->token, 'redirect_url' => $response->redirect_url];
    }

    /** @return array<string, mixed> */
    public function status(string $providerOrderId): array
    {
        return json_decode(json_encode(Transaction::status($providerOrderId), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    public function cancel(string $providerOrderId): void
    {
        Transaction::cancel($providerOrderId);
    }
}
