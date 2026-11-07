@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('content')
<div class="admin-page-heading"><h1>Dashboard Admin</h1><p>Kelola pesanan, produk, dan operasional UMKM Pangkonan.</p></div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3"><div class="card metric-card"><div class="d-flex gap-3"><span class="metric-icon"><x-icon name="cart" /></span><div><div class="metric-label">Total Pesanan</div><div class="metric-value">{{ $orderCount }}</div><div class="metric-help">{{ $openOrderCount }} perlu diproses</div></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card metric-card"><div class="d-flex gap-3"><span class="metric-icon green"><x-icon name="chart" /></span><div><div class="metric-label">Pendapatan</div><div class="metric-value">Rp {{ number_format($revenue, 0, ',', '.') }}</div><div class="metric-help">Pesanan telah dibayar</div></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card metric-card"><div class="d-flex gap-3"><span class="metric-icon gold"><x-icon name="product" /></span><div><div class="metric-label">Produk Aktif</div><div class="metric-value">{{ $productCount }}</div><div class="metric-help">{{ $lowStockCount }} stok rendah</div></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card metric-card"><div class="d-flex gap-3"><span class="metric-icon purple"><x-icon name="users" /></span><div><div class="metric-label">Pelanggan</div><div class="metric-value">{{ $customerCount }}</div><div class="metric-help">Akun pelanggan terdaftar</div></div></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <section class="card dashboard-panel" aria-labelledby="recent-order-heading">
            <div class="dashboard-panel-header"><h2 id="recent-order-heading">Pesanan Terbaru</h2><a href="{{ route('admin.orders.index') }}">Lihat Semua <x-icon name="arrow" size="12" /></a></div>
            <div class="table-responsive"><table class="table"><thead><tr><th># Pesanan</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead><tbody>
                @forelse($recentOrders as $order)
                    @php($statusClass = in_array($order->order_status->value, ['completed', 'confirmed']) ? '' : (in_array($order->order_status->value, ['cancelled', 'exception']) ? 'danger' : 'warning'))
                    <tr><td><a class="fw-bold text-decoration-none" href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td><td>{{ $order->user->name }}</td><td>Rp {{ number_format($order->total, 0, ',', '.') }}</td><td>{{ strtoupper($order->payment_method) }}</td><td><span class="status-chip {{ $statusClass }}">{{ str_replace('_', ' ', $order->order_status->value) }}</span></td></tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody></table></div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="card dashboard-panel" aria-labelledby="stock-heading">
            <div class="dashboard-panel-header"><h2 id="stock-heading">Ringkasan Stok</h2><a href="{{ route('admin.inventory.index') }}">Lihat Semua <x-icon name="arrow" size="12" /></a></div>
            <div class="stock-list">
                @forelse($lowStockProducts as $product)
                    <div class="stock-row"><div class="stock-mini"><x-product-visual :product="$product" /></div><span>{{ $product->name }}</span><strong>{{ $product->available_stock }}</strong></div>
                @empty
                    <div class="text-muted small py-4 text-center">Belum ada data stok.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <section class="card revenue-card" aria-labelledby="revenue-heading">
            <div class="revenue-copy"><small id="revenue-heading">Pendapatan 7 Hari Terakhir</small><strong>Rp {{ number_format($dailyRevenue->sum('amount'), 0, ',', '.') }}</strong><span class="text-green small fw-bold">Data transaksi terbayar</span></div>
            @php($maxRevenue = max(1, $dailyRevenue->max('amount')))
            <div class="mini-chart" aria-label="Grafik pendapatan tujuh hari">
                @foreach($dailyRevenue as $day)<div class="mini-chart-bar" style="height: {{ max(12, round(($day['amount'] / $maxRevenue) * 100)) }}%" title="{{ $day['label'] }}: Rp {{ number_format($day['amount'], 0, ',', '.') }}"></div>@endforeach
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="card dashboard-panel" aria-labelledby="exception-heading">
            <div class="dashboard-panel-header"><h2 id="exception-heading"><span class="text-danger">⚠</span> Exception Pembayaran</h2><a href="{{ route('admin.payments.index') }}">Buka Pembayaran</a></div>
            <div class="exception-list">
                @forelse($exceptions as $order)<div class="exception-row"><span><strong>{{ $order->order_number }}</strong> · {{ str($order->exception_reason)->limit(46) }}</span><a class="btn btn-sm btn-outline-brand" href="{{ route('admin.orders.show', $order) }}">Cek Detail</a></div>
                @empty<div class="text-muted small py-3 text-center">Tidak ada exception pembayaran aktif.</div>@endforelse
            </div>
        </section>
    </div>
</div>
@endsection
