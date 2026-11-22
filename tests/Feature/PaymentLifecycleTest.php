<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\MidtransGateway;
use App\Services\PaymentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Tests\TestCase;

class PaymentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_button_opens_midtrans_and_reuses_the_existing_session(): void
    {
        [$order, $payment] = $this->pendingOrder();
        $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/test-token';
        $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($redirectUrl): void {
            $mock->shouldReceive('createTransaction')->once()
                ->andReturn(['token' => 'test-token', 'redirect_url' => $redirectUrl]);
        });

        $this->actingAs($order->user)->post(route('payments.initialize', $order))->assertRedirect($redirectUrl);
        $this->post(route('payments.initialize', $order))->assertRedirect($redirectUrl);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id, 'status' => PaymentStatus::Pending->value,
            'snap_token' => 'test-token', 'redirect_url' => $redirectUrl,
        ]);
        $this->assertSame(PaymentStatus::Unpaid, $order->refresh()->payment_status);
    }

    public function test_midtrans_failure_returns_an_error_without_marking_the_order_paid(): void
    {
        [$order, $payment, $product] = $this->pendingOrder();
        config(['pangkonan.midtrans.server_key' => 'test-private-key']);
        Log::spy();
        $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createTransaction')->once()
                ->andThrow(new Exception('Access denied: test-private-key', 401));
        });

        $this->actingAs($order->user)->from(route('orders.show', $order))
            ->post(route('payments.initialize', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHasErrors(['payment' => 'Sesi pembayaran belum dapat dibuat. Silakan coba lagi atau hubungi toko.']);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'snap_token' => null, 'redirect_url' => null]);
        $this->assertSame(PaymentStatus::Unpaid, $order->refresh()->payment_status);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_on_hand' => 5, 'stock_reserved' => 2]);
        Log::shouldHaveReceived('warning')->once()->with('Gagal membuat sesi pembayaran Midtrans', \Mockery::on(
            fn (array $context): bool => $context['payment_id'] === $payment->id
                && $context['error_code'] === 401 && $context['error'] === 'Access denied: [redacted]',
        ));
    }

    public function test_paid_is_idempotent_and_a_late_pending_event_cannot_downgrade_it(): void
    {
        [$order, $payment, $product] = $this->pendingOrder();
        $service = app(PaymentService::class);

        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement'));
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement'));
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'pending'));

        $this->assertSame(PaymentStatus::Paid, $payment->refresh()->status);
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        $this->assertSame(OrderStatus::Confirmed, $order->order_status);
        $this->assertSame(3, $product->refresh()->stock_on_hand);
        $this->assertSame(0, $product->stock_reserved);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_verified_expiry_releases_once_and_late_payment_is_visible_as_exception(): void
    {
        [$order, $payment, $product] = $this->pendingOrder();
        $service = app(PaymentService::class);

        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'expire'));
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'expire'));

        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->order_status);
        $this->assertSame(5, $product->refresh()->stock_on_hand);
        $this->assertSame(0, $product->stock_reserved);
        $this->assertDatabaseCount('stock_movements', 2);

        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement'));

        $this->assertSame(PaymentStatus::Paid, $payment->refresh()->status);
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        $this->assertSame(OrderStatus::Exception, $order->order_status);
        $this->assertNotNull($order->exception_reason);
        $this->assertSame(5, $product->refresh()->stock_on_hand);
    }

    public function test_refund_adjustments_are_cumulative_and_idempotent(): void
    {
        [$order, $payment] = $this->pendingOrder();
        $service = app(PaymentService::class);
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement'));

        $partial = $this->gatewayStatus($payment, 'partial_refund', ['refund_amount' => '10000.00']);
        $service->applyVerifiedStatus($payment, $partial);
        $service->applyVerifiedStatus($payment, $partial);
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'refund', ['refund_amount' => $payment->amount.'.00']));

        $this->assertSame(PaymentStatus::Refunded, $payment->refresh()->status);
        $this->assertSame(PaymentStatus::Refunded, $order->refresh()->payment_status);
        $this->assertSame($payment->amount, (int) $payment->adjustments()->sum('amount'));
        $this->assertSame(2, $payment->adjustments()->count());
    }

    public function test_second_paid_attempt_marks_overpayment_without_double_committing_stock(): void
    {
        [$order, $first, $product] = $this->pendingOrder();
        $service = app(PaymentService::class);
        $service->applyVerifiedStatus($first, $this->gatewayStatus($first, 'settlement'));
        $second = $order->payments()->create([
            'attempt_number' => 2,
            'provider_order_id' => $order->order_number.'-A2-TEST',
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'currency' => 'IDR',
        ]);

        $service->applyVerifiedStatus($second, $this->gatewayStatus($second, 'settlement', ['transaction_id' => 'TX-SECOND']));

        $this->assertSame(PaymentStatus::Paid, $second->refresh()->status);
        $this->assertSame(OrderStatus::Exception, $order->refresh()->order_status);
        $this->assertStringContainsString('Lebih dari satu', (string) $order->exception_reason);
        $this->assertSame(3, $product->refresh()->stock_on_hand);
        $this->assertSame(1, $second->adjustments()->where('type', 'overpayment')->count());
    }

    public function test_amount_currency_merchant_and_fraud_are_validated(): void
    {
        [, $payment, $product] = $this->pendingOrder();
        $service = app(PaymentService::class);

        try {
            $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement', ['gross_amount' => '1.00']));
            $this->fail('Nominal berbeda seharusnya ditolak.');
        } catch (ValidationException) {
            $this->assertSame(PaymentStatus::Pending, $payment->refresh()->status);
        }

        config(['pangkonan.midtrans.merchant_id' => 'EXPECTED']);
        $this->expectException(ValidationException::class);
        $service->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement', ['merchant_id' => 'OTHER']));
        $this->assertSame(2, $product->refresh()->stock_reserved);
    }

    public function test_non_accepted_settlement_is_held_for_review(): void
    {
        [$order, $payment, $product] = $this->pendingOrder();

        app(PaymentService::class)->applyVerifiedStatus($payment, $this->gatewayStatus($payment, 'settlement', ['fraud_status' => 'challenge']));

        $this->assertSame(PaymentStatus::Challenge, $payment->refresh()->status);
        $this->assertSame(PaymentStatus::Challenge, $order->refresh()->payment_status);
        $this->assertSame(5, $product->refresh()->stock_on_hand);
        $this->assertSame(2, $product->stock_reserved);
    }

    public function test_duplicate_webhook_is_durable_and_processed_once(): void
    {
        [$order, $payment, $product] = $this->pendingOrder();
        config(['pangkonan.midtrans.server_key' => 'server-key']);
        $payload = $this->gatewayStatus($payment, 'settlement', ['status_code' => '200', 'gross_amount' => $payment->amount.'.00']);
        $payload['signature_key'] = hash('sha512', $payment->provider_order_id.'200'.$payload['gross_amount'].'server-key');
        $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($payload, $payment): void {
            $mock->shouldReceive('status')->once()->with($payment->provider_order_id)->andReturn($payload);
        });

        $invalid = $payload;
        $invalid['signature_key'] = 'invalid';
        $this->postJson(route('payments.webhook'), $invalid)->assertForbidden();
        $this->assertDatabaseCount('payment_notifications', 0);

        $this->postJson(route('payments.webhook'), $payload)->assertOk();
        $this->postJson(route('payments.webhook'), $payload)->assertOk()->assertJson(['message' => 'Event sudah diproses.']);

        $this->assertDatabaseCount('payment_notifications', 1);
        $this->assertSame(OrderStatus::Confirmed, $order->refresh()->order_status);
        $this->assertSame(3, $product->refresh()->stock_on_hand);
    }

    public function test_gateway_failure_keeps_webhook_durable_for_reconciliation(): void
    {
        [, $payment] = $this->pendingOrder();
        config(['pangkonan.midtrans.server_key' => 'server-key']);
        $payload = $this->gatewayStatus($payment, 'settlement', ['status_code' => '200', 'gross_amount' => $payment->amount.'.00']);
        $payload['signature_key'] = hash('sha512', $payment->provider_order_id.'200'.$payload['gross_amount'].'server-key');
        $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($payment): void {
            $mock->shouldReceive('status')->once()->with($payment->provider_order_id)->andThrow(new \RuntimeException('Gateway sementara tidak tersedia'));
        });

        $this->postJson(route('payments.webhook'), $payload)->assertStatus(503);

        $this->assertDatabaseHas('payment_notifications', [
            'provider_order_id' => $payment->provider_order_id,
            'processed_at' => null,
            'attempts' => 1,
        ]);
        $this->assertSame(PaymentStatus::Pending, $payment->refresh()->status);
    }

    /** @return array{Order, Payment, Product} */
    private function pendingOrder(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25000, 'stock_on_hand' => 5, 'stock_reserved' => 0]);
        $cart = Cart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        $order = app(CheckoutService::class)->checkout($user, [
            'recipient_name' => 'Pelanggan Test',
            'recipient_phone' => '08123456789',
            'fulfillment_method' => 'pickup',
            'delivery_zone_id' => null,
            'delivery_address' => null,
            'payment_method' => 'midtrans',
            'customer_notes' => null,
        ], (string) Str::uuid());
        $payment = $order->payments()->create([
            'attempt_number' => 1,
            'provider_order_id' => $order->order_number.'-A1-TEST',
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'currency' => 'IDR',
        ]);

        return [$order, $payment, $product];
    }

    /** @param array<string, mixed> $overrides @return array<string, mixed> */
    private function gatewayStatus(Payment $payment, string $transactionStatus, array $overrides = []): array
    {
        return array_merge([
            'order_id' => $payment->provider_order_id,
            'transaction_id' => 'TX-'.$payment->id,
            'transaction_status' => $transactionStatus,
            'gross_amount' => $payment->amount.'.00',
            'currency' => 'IDR',
            'fraud_status' => 'accept',
        ], $overrides);
    }
}
