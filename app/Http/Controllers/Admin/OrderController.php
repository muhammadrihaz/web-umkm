<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FulfillmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()->with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('order_status', $request->string('status')))
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('order_number', 'like', '%'.$request->string('q').'%')->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%'.$request->string('q').'%'))))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('user', 'items', 'payments', 'histories')]);
    }

    public function confirmCod(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $service->confirmCod($order, $request->user());

        return back()->with('success', 'COD dikonfirmasi dan stok dikomit.');
    }

    public function receiveCod(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $service->receiveCod($order, $request->user());

        return back()->with('success', 'Penerimaan COD dicatat.');
    }

    public function fulfillment(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(FulfillmentStatus::class)]]);
        $service->updateFulfillment($order, FulfillmentStatus::from($data['status']), $request->user());

        return back()->with('success', 'Status pemenuhan diperbarui.');
    }
}
