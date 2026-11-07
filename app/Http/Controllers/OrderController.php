<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', ['orders' => $request->user()->orders()->latest()->paginate(15)]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('orders.show', ['order' => $order->load('items', 'payments', 'histories')]);
    }

    public function cancel(Request $request, Order $order, OrderStatusService $service): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $service->cancelUnpaid($order, $request->user(), $data['reason']);

        return back()->with('success', 'Pesanan dibatalkan dan reservasi stok dilepas.');
    }
}
