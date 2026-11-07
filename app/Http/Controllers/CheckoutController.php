<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\DeliveryZone;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View
    {
        $cart = Cart::query()->with('items.product.images')->where('user_id', $request->user()->id)->first();
        abort_if(! $cart || $cart->items->isEmpty(), 422, 'Keranjang masih kosong.');

        return view('checkout.show', ['cart' => $cart, 'zones' => DeliveryZone::query()->where('is_active', true)->orderBy('name')->get(), 'idempotencyKey' => (string) Str::uuid()]);
    }

    public function store(CheckoutRequest $request, CheckoutService $service): RedirectResponse
    {
        $order = $service->checkout($request->user(), $request->safe()->except('idempotency_key'), $request->string('idempotency_key')->toString());

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan berhasil dibuat.');
    }
}
