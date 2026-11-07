@extends('layouts.app')
@section('title', 'Beranda · '.config('app.name'))
@section('content')
<div class="page-shell">
    <section class="home-hero">
        <x-hero-art />
        <div class="hero-copy">
            <span class="eyebrow">UMKM Lokal Gianyar, Bali</span>
            <h1>Oleh-Oleh Khas Bali dari UMKM <span>Pangkonan</span></h1>
            <p>Aneka camilan khas Bali dengan cita rasa autentik, berkualitas, dan diproduksi oleh UMKM lokal di Gianyar.</p>
            <div class="hero-actions">
                <a href="{{ route('catalog.index') }}" class="btn btn-brand btn-lg"><x-icon name="cart" size="19" /> Belanja Sekarang</a>
                @if(config('pangkonan.business.whatsapp'))
                    <a class="btn btn-whatsapp btn-lg" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D/', '', config('pangkonan.business.whatsapp')) }}?text={{ urlencode('Halo, saya ingin bertanya tentang produk Pangkonan.') }}"><x-icon name="whatsapp" size="21" /> Hubungi WhatsApp</a>
                @else
                    <a class="btn btn-whatsapp btn-lg" href="{{ route('contact') }}"><x-icon name="whatsapp" size="21" /> Hubungi Kami</a>
                @endif
            </div>
        </div>
    </section>

    <section class="home-section" aria-labelledby="category-heading">
        <div class="section-heading">
            <div><h2 id="category-heading">Kategori Produk</h2><p>Jelajahi berbagai pilihan oleh-oleh khas Bali dari Pangkonan.</p></div>
        </div>
        <div class="category-grid">
            <a class="category-tile active" href="{{ route('catalog.index') }}"><x-icon class="category-icon" name="grid" size="28" /><strong>Semua Produk</strong></a>
            @php($categoryIcons = ['product', 'stock', 'gift', 'grid', 'gift'])
            @foreach($categories->take(5) as $category)
                <a class="category-tile" href="{{ route('catalog.index', ['category' => $category->slug]) }}">
                    <x-icon class="category-icon" :name="$categoryIcons[$loop->index % count($categoryIcons)]" size="28" />
                    <strong>{{ $category->name }}</strong>
                </a>
            @endforeach
            @for($index = $categories->count(); $index < 5; $index++)
                <a class="category-tile" href="{{ route('catalog.index') }}"><x-icon class="category-icon" :name="$categoryIcons[$index]" size="28" /><strong>{{ ['Pia Bali', 'Kacang & Camilan', 'Keripik', 'Manisan', 'Paket Oleh-Oleh'][$index] }}</strong></a>
            @endfor
        </div>
    </section>

    <section class="home-section pt-1" aria-labelledby="featured-heading">
        <div class="section-heading">
            <div><h2 id="featured-heading">Produk Unggulan</h2><p>Produk pilihan UMKM Pangkonan yang paling diminati pelanggan.</p></div>
            <a class="section-link" href="{{ route('catalog.index') }}">Lihat Semua Produk <x-icon name="arrow" size="15" /></a>
        </div>
        <div class="row product-grid">
            @forelse($products->take(4) as $product)
                <div class="col-sm-6 col-xl-3">@include('catalog._card', ['product' => $product])</div>
            @empty
                <div class="col"><div class="alert alert-light border">Belum ada produk aktif.</div></div>
            @endforelse
        </div>
    </section>

    <section class="benefit-band" id="keunggulan" aria-labelledby="benefit-heading">
        <h2 id="benefit-heading">Keunggulan Belanja di Pangkonan</h2>
        <div class="benefit-grid">
            <div class="benefit-item"><span class="benefit-icon green"><x-icon name="shield" size="34" /></span><div><strong>Produk UMKM Lokal</strong><small>Mendukung usaha kecil di Gianyar, Bali.</small></div></div>
            <div class="benefit-item"><span class="benefit-icon"><x-icon name="gift" size="34" /></span><div><strong>Kualitas Terjamin</strong><small>Bahan pilihan, rasa autentik, dan higienis.</small></div></div>
            <div class="benefit-item"><span class="benefit-icon"><x-icon name="truck" size="35" /></span><div><strong>Pickup & Delivery Lokal</strong><small>Bisa ambil di toko atau dikirim ke area sekitar Gianyar.</small></div></div>
            <div class="benefit-item"><span class="benefit-icon"><x-icon name="headset" size="34" /></span><div><strong>Layanan Ramah</strong><small>Siap membantu melalui WhatsApp.</small></div></div>
        </div>
    </section>

    <section class="delivery-band" aria-labelledby="delivery-heading">
        <div class="delivery-intro"><div class="delivery-scene"></div><div><h2 id="delivery-heading">Pickup & Delivery Lokal<br>Pangkonan – Gianyar, Bali</h2><p>Ambil pesanan langsung di lokasi kami atau pesan dengan pengantaran ke area sekitar Gianyar.</p></div></div>
        <div class="delivery-option"><x-icon name="pickup" size="30" /><div><strong>Pickup di Toko</strong><small>Ambil pesanan langsung di UMKM Pangkonan.</small></div></div>
        <div class="delivery-option"><x-icon name="truck" size="31" /><div><strong>Delivery Lokal</strong><small>Pengantaran ke area yang tersedia.</small></div></div>
    </section>
</div>
@endsection
