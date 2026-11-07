<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4d2c22">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <title>@yield('title', 'Admin').' · '.config('app.name')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}"><x-brand compact light /></a>
        @include('admin._nav')
        <div class="admin-sidebar-footer">
            <a class="admin-nav-link" href="{{ route('home') }}"><x-icon name="home" /><span>Lihat Toko</span></a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="admin-nav-link" type="submit"><x-icon name="logout" /><span>Keluar</span></button></form>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-title"><strong>@yield('title', 'Dashboard Admin')</strong><small>Kelola operasional UMKM Pangkonan.</small></div>
            <div class="admin-top-actions">
                <span class="admin-notification" aria-label="Notifikasi"><x-icon name="bell" size="17" /></span>
                <div class="admin-user"><span class="admin-avatar"><x-icon name="user" size="17" /></span><div><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div></div>
            </div>
        </header>
        <main class="admin-content">
            @if(session('success'))<div class="alert alert-success shadow-sm">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger shadow-sm"><strong>Periksa kembali data:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
