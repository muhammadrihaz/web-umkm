<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /** @param array<string, mixed> $data */
    public function checkout(User $user, array $data, string $idempotencyKey): Order
    {
        $fingerprint = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
        $existing = Order::query()->where('user_id', $user->id)->where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            if (! hash_equals($existing->payload_fingerprint, $fingerprint)) {
                throw ValidationException::withMessages(['idempotency_key' => 'Kunci checkout telah digunakan untuk data berbeda.']);
            }

            return $existing;
        }

        $cart = Cart::query()->with('items.product')->where('user_id', $user->id)->first();
        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Keranjang masih kosong.']);
        }

        return DB::transaction(function () use ($user, $data, $idempotencyKey, $fingerprint, $cart): Order {
            $productIds = $cart->items->pluck('product_id')->sort()->values();
            $products = Product::query()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $zone = null;
            $deliveryFee = 0;
            if ($data['fulfillment_method'] === 'delivery') {
                $zone = DeliveryZone::query()->whereKey($data['delivery_zone_id'])->where('is_active', true)->firstOrFail();
                $deliveryFee = $zone->fee;
            }

            $subtotal = 0;
            foreach ($cart->items as $cartItem) {
                $product = $products->get($cartItem->product_id);
                if (! $product || ! $product->is_active || $product->available_stock < $cartItem->quantity) {
                    throw ValidationException::withMessages(['cart' => "Produk {$cartItem->product?->name} tidak tersedia dalam jumlah yang diminta."]);
                }
                $subtotal += $product->price * $cartItem->quantity;
            }

            $paymentMethod = $data['payment_method'];
            if ($paymentMethod === 'cod' && ! config('pangkonan.cod_enabled')) {
                throw ValidationException::withMessages(['payment_method' => 'COD sedang tidak tersedia.']);
            }
            $order = $user->orders()->create([
                'delivery_zone_id' => $zone?->id,
                'order_number' => 'PGK-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'idempotency_key' => $idempotencyKey,
                'payload_fingerprint' => $fingerprint,
                'order_status' => $paymentMethod === 'cod' ? OrderStatus::AwaitingConfirmation : OrderStatus::AwaitingPayment,
                'payment_status' => PaymentStatus::Unpaid,
                'fulfillment_status' => FulfillmentStatus::Unfulfilled,
                'payment_method' => $paymentMethod,
                'fulfillment_method' => $data['fulfillment_method'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'delivery_address' => $data['fulfillment_method'] === 'delivery' ? $data['delivery_address'] : null,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);

            foreach ($cart->items as $cartItem) {
                $product = $products->get($cartItem->product_id);
                $order->items()->create([
                    'product_id' => $product->id, 'sku' => $product->sku, 'product_name' => $product->name,
                    'unit' => $product->unit, 'unit_price' => $product->price, 'quantity' => $cartItem->quantity,
                    'line_total' => $product->price * $cartItem->quantity,
                ]);
                $this->inventoryService->reserve($order, $product, $cartItem->quantity);
            }
            $order->histories()->create(['source' => 'customer', 'state_type' => 'order', 'to_status' => $order->order_status->value, 'reason' => 'Checkout dibuat']);
            $cart->items()->delete();

            return $order->load('items', 'reservations');
        }, 3);
    }
}
