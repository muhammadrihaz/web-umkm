<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_lifecycle_commits_stock_once_and_requires_receipt_before_completion(): void
    {
        [$order, $product, $admin] = $this->codOrder();
        $service = app(OrderStatusService::class);

        $service->confirmCod($order, $admin);
        $service->updateFulfillment($order->refresh(), FulfillmentStatus::Preparing, $admin);
        $service->updateFulfillment($order->refresh(), FulfillmentStatus::ReadyForPickup, $admin);
        try {
            $service->updateFulfillment($order->refresh(), FulfillmentStatus::PickedUp, $admin);
            $this->fail('COD belum diterima seharusnya tidak dapat diselesaikan.');
        } catch (ValidationException) {
            $this->assertSame(OrderStatus::Processing, $order->refresh()->order_status);
        }
        $service->receiveCod($order->refresh(), $admin);
        $service->receiveCod($order->refresh(), $admin);
        $service->updateFulfillment($order->refresh(), FulfillmentStatus::PickedUp, $admin);

        $this->assertSame(OrderStatus::Completed, $order->refresh()->order_status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(3, $product->refresh()->stock_on_hand);
        $this->assertSame(0, $product->stock_reserved);
        $this->assertSame(1, $order->histories()->where('state_type', 'payment')->count());
    }

    public function test_cod_receipt_before_confirmation_is_rejected(): void
    {
        [$order, , $admin] = $this->codOrder();

        $this->expectException(ValidationException::class);
        app(OrderStatusService::class)->receiveCod($order, $admin);
    }

    public function test_fulfillment_cannot_skip_states_or_use_delivery_state_for_pickup(): void
    {
        [$order, , $admin] = $this->codOrder();
        $service = app(OrderStatusService::class);
        $service->confirmCod($order, $admin);

        try {
            $service->updateFulfillment($order->refresh(), FulfillmentStatus::ReadyForPickup, $admin);
            $this->fail('Status pemenuhan tidak boleh dilompati.');
        } catch (ValidationException) {
            $this->assertSame(FulfillmentStatus::Unfulfilled, $order->refresh()->fulfillment_status);
        }

        $this->expectException(ValidationException::class);
        $service->updateFulfillment($order->refresh(), FulfillmentStatus::OutForDelivery, $admin);
    }

    public function test_processed_order_cannot_be_cancelled_through_unpaid_customer_flow(): void
    {
        [$order, , $admin] = $this->codOrder();
        $service = app(OrderStatusService::class);
        $service->confirmCod($order, $admin);

        $this->expectException(ValidationException::class);
        $service->cancelUnpaid($order->refresh(), $order->user, 'Berubah pikiran');
    }

    /** @return array{Order, Product, User} */
    private function codOrder(): array
    {
        config(['pangkonan.cod_enabled' => true]);
        $customer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock_on_hand' => 5, 'stock_reserved' => 0]);
        $cart = Cart::query()->create(['user_id' => $customer->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        $order = app(CheckoutService::class)->checkout($customer, [
            'recipient_name' => 'Pelanggan COD',
            'recipient_phone' => '08123456789',
            'fulfillment_method' => 'pickup',
            'delivery_zone_id' => null,
            'delivery_address' => null,
            'payment_method' => 'cod',
            'customer_notes' => null,
        ], (string) Str::uuid());

        return [$order, $product, $admin];
    }
}
