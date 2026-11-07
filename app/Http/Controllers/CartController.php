<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = Cart::query()->firstOrCreate(['user_id' => $request->user()->id]);

        return view('cart.index', ['cart' => $cart->load('items.product.images')]);
    }

    public function store(CartItemRequest $request): RedirectResponse
    {
        $product = Product::query()->whereKey($request->integer('product_id'))->where('is_active', true)->firstOrFail();
        $quantity = $request->integer('quantity');
        abort_if($quantity > $product->available_stock, 422, 'Stok tidak mencukupi.');
        $cart = Cart::query()->firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $newQuantity = $item->exists ? $item->quantity + $quantity : $quantity;
        if ($newQuantity > $product->available_stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok tersedia.']);
        }
        $item->quantity = $newQuantity;
        $item->save();

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(CartItemRequest $request, int $item): RedirectResponse
    {
        $cart = Cart::query()->where('user_id', $request->user()->id)->firstOrFail();
        $cartItem = $cart->items()->with('product')->findOrFail($item);
        if ($request->integer('quantity') > $cartItem->product->available_stock) {
            return back()->withErrors(['quantity' => 'Jumlah melebihi stok tersedia.']);
        }
        $cartItem->update(['quantity' => $request->integer('quantity')]);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $cart = Cart::query()->where('user_id', $request->user()->id)->firstOrFail();
        $cart->items()->findOrFail($item)->delete();

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
