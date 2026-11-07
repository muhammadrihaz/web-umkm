@extends('layouts.app')
@section('title', $product->name.' · '.config('app.name'))
@section('content')
<div class="content-page product-detail">
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item"><a href="{{ route('catalog.index') }}">Katalog</a></li><li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li></ol></nav>
    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="detail-main-visual"><x-product-visual :product="$product" /></div>
            <div class="detail-thumbs" aria-hidden="true">
                @for($thumb = 0; $thumb < 4; $thumb++)<div class="detail-thumb {{ $thumb === 0 ? 'active' : '' }}"><x-product-visual :product="$product" /></div>@endfor
            </div>
        </div>
        <div class="col-lg-5 detail-copy">
            <div class="small text-muted mb-1">{{ $product->category?->name ?? 'Produk Pangkonan' }}</div>
            <h1>{{ $product->name }}</h1>
            <div class="detail-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
            <span class="stock-pill"><x-icon name="stock" size="14" /> Stok Tersedia ({{ $product->available_stock }})</span>
            <p class="detail-description">{{ $product->description ?: 'Camilan khas Bali dengan rasa autentik, dibuat oleh UMKM lokal Pangkonan di Gianyar.' }}</p>
            @auth
                @unless(auth()->user()->isAdmin())
                    <form method="post" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="d-flex align-items-center gap-3 mt-3"><strong class="small">Jumlah</strong><div class="quantity-control"><button type="button" data-quantity-minus aria-label="Kurangi jumlah">−</button><input id="product-quantity" type="number" name="quantity" min="1" max="{{ max(1, $product->available_stock) }}" value="1"><button type="button" data-quantity-plus aria-label="Tambah jumlah">+</button></div></div>
                        <div class="detail-actions"><button class="btn btn-brand" @disabled($product->available_stock < 1)><x-icon name="cart" size="18" /> Tambah ke Keranjang</button>
                        @if(config('pangkonan.business.whatsapp'))<a class="btn btn-whatsapp" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D/', '', config('pangkonan.business.whatsapp')) }}?text={{ urlencode('Halo, saya ingin bertanya tentang '.$product->name) }}"><x-icon name="whatsapp" size="18" /> WhatsApp</a>@endif</div>
                    </form>
                @else
                    <div class="detail-actions"><a class="btn btn-brand" href="{{ route('admin.products.edit', $product) }}">Kelola Produk</a></div>
                @endunless
            @else
                <div class="detail-actions"><a href="{{ route('login') }}" class="btn btn-brand"><x-icon name="cart" size="18" /> Masuk untuk Memesan</a></div>
            @endauth
        </div>
        <aside class="col-lg-2">
            <div class="pickup-card">
                <h2>Pickup & Delivery</h2>
                <div class="pickup-row"><x-icon name="pickup" size="25" /><div><strong>Pickup di Toko</strong><small>Bisa diambil langsung di lokasi UMKM.</small></div></div>
                <div class="pickup-row"><x-icon name="truck" size="25" /><div><strong>Delivery Lokal</strong><small>Pengantaran ke area Gianyar yang tersedia.</small></div></div>
            </div>
        </aside>
    </div>
    @if($relatedProducts->isNotEmpty())
        <section class="related-strip" aria-labelledby="related-heading">
            <div class="section-heading"><div><h2 id="related-heading">Produk Terkait</h2></div><a class="section-link" href="{{ route('catalog.index') }}">Lihat Semua <x-icon name="arrow" size="14" /></a></div>
            <div class="row product-grid">@foreach($relatedProducts as $relatedProduct)<div class="col-sm-6 col-lg-3">@include('catalog._card', ['product' => $relatedProduct])</div>@endforeach</div>
        </section>
    @endif
</div>
@endsection
@push('scripts')
<script>
    const quantityInput = document.getElementById('product-quantity');
    if (quantityInput) {
        document.querySelector('[data-quantity-minus]').addEventListener('click', () => quantityInput.value = Math.max(Number(quantityInput.min), Number(quantityInput.value) - 1));
        document.querySelector('[data-quantity-plus]').addEventListener('click', () => quantityInput.value = Math.min(Number(quantityInput.max), Number(quantityInput.value) + 1));
    }
</script>
@endpush
