@extends('layouts.app')
@section('title', 'Keranjang · '.config('app.name'))
@section('content')
<div class="content-page">
    <div class="page-title-row"><div><h1>Keranjang Belanja</h1><p>Periksa kembali produk dan jumlah sebelum melanjutkan.</p></div><a class="btn btn-outline-brand" href="{{ route('catalog.index') }}">Lanjut Belanja</a></div>
    @if($cart->items->isEmpty())
        <div class="text-center py-5"><x-icon name="cart" size="54" class="text-muted mb-3" /><h2 class="h4">Keranjang masih kosong</h2><p class="text-muted">Pilih produk khas Bali favorit Anda dari katalog.</p><a class="btn btn-brand" href="{{ route('catalog.index') }}">Lihat Katalog</a></div>
    @else
        <div class="card overflow-hidden"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th><span class="visually-hidden">Aksi</span></th></tr></thead><tbody>
            @foreach($cart->items as $item)
                <tr>
                    <td><div class="cart-product"><x-product-visual :product="$item->product" /><div><strong>{{ $item->product->name }}</strong><div class="small text-muted">{{ $item->product->category?->name ?? $item->product->sku }}</div></div></div></td>
                    <td>Rp {{ number_format($item->product->price, 0, ',', '.') }}</td>
                    <td><form method="post" action="{{ route('cart.update', $item->id) }}" class="d-flex gap-2">@csrf @method('put')<input type="hidden" name="product_id" value="{{ $item->product_id }}"><input class="form-control" style="width:76px" type="number" name="quantity" min="1" max="{{ $item->product->available_stock }}" value="{{ $item->quantity }}" aria-label="Jumlah {{ $item->product->name }}"><button class="btn btn-sm btn-outline-brand">Ubah</button></form></td>
                    <td class="fw-bold">Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</td>
                    <td><form method="post" action="{{ route('cart.destroy', $item->id) }}" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $item->product->name }}"><x-icon name="trash" size="15" /></button></form></td>
                </tr>
            @endforeach
        </tbody></table></div></div>
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mt-3 p-3 rounded-4" style="background:#fff5e9"><div><span class="text-muted small">Subtotal</span><div class="h4 mb-0 text-brand">Rp {{ number_format($cart->items->sum(fn ($item) => $item->product->price * $item->quantity), 0, ',', '.') }}</div></div><a class="btn btn-brand btn-lg" href="{{ route('checkout.show') }}">Lanjut Checkout <x-icon name="arrow" size="18" /></a></div>
    @endif
</div>
@endsection
