<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b84f2e">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    $cartCount = auth()->check() && ! auth()->user()->isAdmin()
        ? (auth()->user()->cart?->items()->sum('quantity') ?? 0)
        : 0;
@endphp
<header class="app-header">
    <nav class="navbar navbar-expand-lg" aria-label="Navigasi utama">
        <div class="container">
            <a href="{{ route('home') }}" aria-label="Pangkonan - Beranda">
                <x-brand />
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavigation">
                <div class="navbar-nav main-nav me-auto">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a>
                    <a class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Katalog</a>
                    <a class="nav-link" href="{{ route('home') }}#keunggulan">Tentang</a>
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Kontak</a>
                </div>
                <form class="header-search" action="{{ route('catalog.index') }}" method="get" role="search">
                    <label class="visually-hidden" for="header-search">Cari produk</label>
                    <input id="header-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari produk (mis. pia, kacang, dll)...">
                    <button type="submit" aria-label="Cari"><x-icon name="search" size="17" /></button>
                </form>
                <div class="header-actions">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a class="header-login" href="{{ route('admin.dashboard') }}"><x-icon name="chart" size="17" /> Admin</a>
                        @else
                            <a class="header-cart" href="{{ route('cart.index') }}" aria-label="Keranjang, {{ $cartCount }} produk">
                                <x-icon name="cart" size="26" />
                                <span class="cart-count">{{ $cartCount }}</span>
                            </a>
                        @endif
                        <div class="dropdown user-menu">
                            <button class="btn btn-link dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                                <x-icon name="user" size="18" /> {{ str(auth()->user()->name)->before(' ') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @unless(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('orders.index') }}">Pesanan saya</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil & alamat</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endunless
                                <li>
                                    <form method="post" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item" type="submit">Keluar</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a class="header-login" href="{{ route('login') }}"><x-icon name="user" size="17" /> Masuk</a>
                        <a class="header-cart" href="{{ route('login') }}" aria-label="Masuk untuk melihat keranjang">
                            <x-icon name="cart" size="26" />
                            <span class="cart-count">0</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

<main class="public-main">
    <div class="container flash-zone pt-3">
        @if(session('success'))
            <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <strong>Periksa kembali data:</strong>
                <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
    </div>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row footer-columns g-4">
            <div class="col-lg-4">
                <x-brand light />
                <p class="mb-0">UMKM oleh-oleh khas Gianyar, Bali. Produk lokal dengan rasa autentik untuk keluarga dan orang tersayang.</p>
            </div>
            <div class="col-6 col-lg-3">
                <div class="footer-title">Kontak & Alamat</div>
                <ul class="footer-list">
                    <li><x-icon name="location" size="14" /> {{ config('pangkonan.business.address') }}</li>
                    @if(config('pangkonan.business.whatsapp'))<li><x-icon name="whatsapp" size="14" /> {{ config('pangkonan.business.whatsapp') }}</li>@endif
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-title">Menu</div>
                <ul class="footer-list">
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('catalog.index') }}">Katalog</a></li>
                    <li><a href="{{ route('home') }}#keunggulan">Tentang</a></li>
                    <li><a href="{{ route('contact') }}">Kontak</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <div class="footer-title">Belanja Lebih Mudah</div>
                <p>Pickup langsung di toko atau gunakan delivery lokal untuk area yang tersedia.</p>
            </div>
        </div>
        <div class="footer-note">{{ config('pangkonan.business.name') }} · Data produk awal berlabel demo sampai dikonfirmasi pemilik.</div>
    </div>
</footer>
@stack('scripts')
</body>
</html>
