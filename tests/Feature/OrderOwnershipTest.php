<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $owner->orders()->create([
            'order_number' => 'PGK-TEST-001', 'idempotency_key' => (string) Str::uuid(), 'payload_fingerprint' => hash('sha256', 'x'),
            'order_status' => OrderStatus::AwaitingPayment, 'payment_status' => PaymentStatus::Unpaid,
            'fulfillment_status' => FulfillmentStatus::Unfulfilled, 'payment_method' => 'midtrans', 'fulfillment_method' => 'pickup',
            'subtotal' => 10000, 'delivery_fee' => 0, 'total' => 10000, 'recipient_name' => 'Owner', 'recipient_phone' => '08123',
        ]);

        $this->actingAs($intruder)->get(route('orders.show', $order))->assertForbidden();
    }
}
