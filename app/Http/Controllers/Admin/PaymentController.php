<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::query()->with(['order.user', 'adjustments'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('provider_order_id', 'like', '%'.$request->string('q').'%')->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', '%'.$request->string('q').'%'))))
            ->latest()->paginate(30)->withQueryString();
        $exceptions = Order::query()->where('order_status', OrderStatus::Exception)->latest()->limit(20)->get();

        return view('admin.payments.index', compact('payments', 'exceptions'));
    }
}
