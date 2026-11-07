<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /** @return array<string, int> */
    public function summary(string $startDate, string $endDate): array
    {
        [$startUtc, $endUtc] = $this->utcBounds($startDate, $endDate);
        $created = Order::query()->whereBetween('created_at', [$startUtc, $endUtc]);
        $paidOrders = Order::query()->whereBetween('paid_at', [$startUtc, $endUtc])->whereIn('payment_status', [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded]);
        $grossReceipts = Payment::query()->whereBetween('paid_at', [$startUtc, $endUtc])->whereIn('status', [PaymentStatus::Paid, PaymentStatus::PartiallyRefunded, PaymentStatus::Refunded])->sum('amount')
            + Order::query()->where('payment_method', 'cod')->whereBetween('cod_received_at', [$startUtc, $endUtc])->sum('total');
        $refunds = DB::table('payment_adjustments')->whereIn('type', ['refund', 'partial_refund'])->whereBetween('occurred_at', [$startUtc, $endUtc])->sum('amount');

        return [
            'orders_created' => (clone $created)->count(),
            'orders_created_value' => (int) (clone $created)->sum('total'),
            'paid_product_value' => (int) (clone $paidOrders)->sum('subtotal'),
            'gross_receipts' => (int) $grossReceipts,
            'delivery_collected' => (int) (clone $paidOrders)->sum('delivery_fee'),
            'refunds' => (int) $refunds,
            'net_receipts' => (int) $grossReceipts - (int) $refunds,
            'cod_unpaid' => (int) Order::query()->where('payment_method', 'cod')->whereNull('cod_received_at')->whereNotIn('order_status', ['cancelled'])->sum('total'),
        ];
    }

    public function orders(string $startDate, string $endDate): Builder
    {
        [$startUtc, $endUtc] = $this->utcBounds($startDate, $endDate);

        return Order::query()->with('user')->whereBetween('created_at', [$startUtc, $endUtc])->latest();
    }

    /** @return array{CarbonImmutable, CarbonImmutable} */
    private function utcBounds(string $startDate, string $endDate): array
    {
        $timezone = config('pangkonan.display_timezone');

        return [CarbonImmutable::parse($startDate, $timezone)->startOfDay()->utc(), CarbonImmutable::parse($endDate, $timezone)->endOfDay()->utc()];
    }
}
