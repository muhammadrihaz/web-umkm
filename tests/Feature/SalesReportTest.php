<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\SalesReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_separates_receipts_refunds_cod_and_product_value(): void
    {
        $paidAt = CarbonImmutable::parse('2026-11-03 04:00:00', 'UTC');
        $online = $this->order('midtrans', 100000, 10000, PaymentStatus::PartiallyRefunded, $paidAt);
        $this->setCreatedAt($online, $paidAt);
        $payment = $online->payments()->create([
            'attempt_number' => 1,
            'provider_order_id' => 'REPORT-ONLINE-1',
            'status' => PaymentStatus::PartiallyRefunded,
            'amount' => 110000,
            'currency' => 'IDR',
            'paid_at' => $paidAt,
        ]);
        $payment->adjustments()->create([
            'provider_adjustment_id' => 'REFUND-REPORT-1',
            'type' => 'partial_refund',
            'amount' => 20000,
            'occurred_at' => $paidAt->addHour(),
            'provider_payload' => [],
        ]);
        $cod = $this->order('cod', 50000, 10000, PaymentStatus::Paid, $paidAt, $paidAt);
        $this->setCreatedAt($cod, $paidAt);
        $unpaidCod = $this->order('cod', 30000, 0, PaymentStatus::Unpaid, null);
        $this->setCreatedAt($unpaidCod, $paidAt);

        $summary = app(SalesReportService::class)->summary('2026-11-03', '2026-11-03');

        $this->assertSame(3, $summary['orders_created']);
        $this->assertSame(200000, $summary['orders_created_value']);
        $this->assertSame(150000, $summary['paid_product_value']);
        $this->assertSame(170000, $summary['gross_receipts']);
        $this->assertSame(20000, $summary['delivery_collected']);
        $this->assertSame(20000, $summary['refunds']);
        $this->assertSame(150000, $summary['net_receipts']);
        $this->assertSame(30000, $summary['cod_unpaid']);
    }

    public function test_wita_day_boundaries_are_converted_to_utc(): void
    {
        $excluded = $this->order('cod', 10000, 0, PaymentStatus::Unpaid, null);
        $included = $this->order('cod', 20000, 0, PaymentStatus::Unpaid, null);
        $this->setCreatedAt($excluded, CarbonImmutable::parse('2026-11-02 15:59:59', 'UTC'));
        $this->setCreatedAt($included, CarbonImmutable::parse('2026-11-02 16:00:00', 'UTC'));

        $summary = app(SalesReportService::class)->summary('2026-11-03', '2026-11-03');

        $this->assertSame(1, $summary['orders_created']);
        $this->assertSame(20000, $summary['orders_created_value']);
    }

    private function order(string $method, int $subtotal, int $deliveryFee, PaymentStatus $paymentStatus, ?CarbonImmutable $paidAt, ?CarbonImmutable $codReceivedAt = null): Order
    {
        $user = User::factory()->create();

        return $user->orders()->create([
            'order_number' => 'REPORT-'.Str::upper(Str::random(12)),
            'idempotency_key' => (string) Str::uuid(),
            'payload_fingerprint' => hash('sha256', Str::random()),
            'order_status' => $paymentStatus === PaymentStatus::Unpaid ? OrderStatus::AwaitingConfirmation : OrderStatus::Confirmed,
            'payment_status' => $paymentStatus,
            'fulfillment_status' => FulfillmentStatus::Unfulfilled,
            'payment_method' => $method,
            'fulfillment_method' => 'pickup',
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'recipient_name' => 'Pelanggan Laporan',
            'recipient_phone' => '08123456789',
            'paid_at' => $paidAt,
            'cod_received_at' => $codReceivedAt,
        ]);
    }

    private function setCreatedAt(Order $order, CarbonImmutable $date): void
    {
        $order->timestamps = false;
        $order->created_at = $date;
        $order->updated_at = $date;
        $order->save();
    }
}
