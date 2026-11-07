<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_is_idempotent_and_stock_commit_is_idempotent(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000, 'stock_on_hand' => 2, 'stock_reserved' => 0]);
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        $key = (string) Str::uuid();
        $data = $this->checkoutData();
        $service = app(CheckoutService::class);

        $first = $service->checkout($user, $data, $key);
        $second = $service->checkout($user, $data, $key);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(2, $product->refresh()->stock_reserved);
        app(InventoryService::class)->commit($first);
        app(InventoryService::class)->commit($first);
        $this->assertSame(0, $product->refresh()->stock_on_hand);
        $this->assertSame(0, $product->stock_reserved);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_same_idempotency_key_with_different_payload_is_rejected(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_on_hand' => 3]);
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
        $key = (string) Str::uuid();
        $service = app(CheckoutService::class);
        $service->checkout($user, $this->checkoutData(), $key);

        $this->expectException(ValidationException::class);
        $service->checkout($user, $this->checkoutData(['recipient_name' => 'Nama Berbeda']), $key);
    }

    public function test_checkout_recalculates_catalog_price_and_ignores_client_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 42000, 'stock_on_hand' => 3]);
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        $payload = $this->checkoutData(['price' => 1, 'total' => 1]);

        $order = app(CheckoutService::class)->checkout($user, $payload, (string) Str::uuid());

        $this->assertSame(84000, $order->subtotal);
        $this->assertSame(84000, $order->total);
        $this->assertSame(42000, $order->items()->firstOrFail()->unit_price);
    }

    public function test_cart_rejects_zero_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('cart.store'), ['product_id' => $product->id, 'quantity' => 0])
            ->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function checkoutData(array $overrides = []): array
    {
        return array_merge(['recipient_name' => 'Pelanggan Demo', 'recipient_phone' => '08123456789', 'fulfillment_method' => 'pickup', 'delivery_zone_id' => null, 'delivery_address' => null, 'payment_method' => 'midtrans', 'customer_notes' => null], $overrides);
    }
}
