<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $displayTimezone = config('pangkonan.display_timezone');
        $paidOrders = Order::query()->whereNotNull('paid_at')->where('paid_at', '>=', now()->subDays(7))->get(['paid_at', 'total']);
        $dailyRevenue = collect(range(6, 0))->map(function (int $daysAgo) use ($displayTimezone, $paidOrders): array {
            $date = now($displayTimezone)->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'amount' => $paidOrders->filter(fn (Order $order): bool => $order->paid_at->timezone($displayTimezone)->isSameDay($date))->sum('total'),
            ];
        });

        return view('admin.dashboard', [
            'orderCount' => Order::query()->count(),
            'openOrderCount' => Order::query()->whereNotIn('order_status', [OrderStatus::Completed, OrderStatus::Cancelled])->count(),
            'customerCount' => User::query()->where('role', 'customer')->count(),
            'productCount' => Product::query()->where('is_active', true)->count(),
            'revenue' => Order::query()->whereNotNull('paid_at')->sum('total'),
            'lowStockCount' => Product::query()->whereRaw('(stock_on_hand - stock_reserved) <= 5')->count(),
            'lowStockProducts' => Product::query()->with('images')->orderByRaw('(stock_on_hand - stock_reserved) asc')->limit(5)->get(),
            'recentOrders' => Order::query()->with('user')->latest()->limit(10)->get(),
            'exceptions' => Order::query()->where('order_status', OrderStatus::Exception)->latest()->limit(4)->get(),
            'dailyRevenue' => $dailyRevenue,
        ]);
    }
}
