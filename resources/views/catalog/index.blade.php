@extends('layouts.app')
@section('title', 'Katalog Produk · '.config('app.name'))
@section('content')
<div class="content-page">
    <nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li><li class="breadcrumb-item active" aria-current="page">Katalog</li></ol></nav>
    <div class="page-title-row">
        <div><h1>Katalog Produk</h1><p>Temukan oleh-oleh khas Bali dari UMKM lokal Gianyar.</p></div>
        <span class="text-muted small">{{ $products->total() }} produk ditemukan</span>
    </div>
    <form class="catalog-filters row g-2 mb-4" method="get">
        <div class="col-md-7"><label class="visually-hidden" for="catalog-search">Cari produk</label><input id="catalog-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama produk atau SKU"></div>
        <div class="col-md-3"><label class="visually-hidden" for="catalog-category">Kategori</label><select id="catalog-category" name="category" class="form-select"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-2 d-grid"><button class="btn btn-brand"><x-icon name="search" size="17" /> Cari</button></div>
    </form>
    <div class="row product-grid catalog-grid">
        @forelse($products as $product)
            <div class="col-sm-6 col-lg-4 col-xl-3">@include('catalog._card')</div>
        @empty
            <div class="col"><div class="alert alert-light border">Produk tidak ditemukan. Coba kata kunci atau kategori lain.</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection
