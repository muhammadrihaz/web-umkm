<nav class="admin-nav" aria-label="Navigasi admin">
    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><x-icon name="home" /><span>Dashboard</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}"><x-icon name="orders" /><span>Pesanan</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><x-icon name="product" /><span>Produk</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><x-icon name="grid" /><span>Kategori</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}" href="{{ route('admin.inventory.index') }}"><x-icon name="stock" /><span>Stok</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}"><x-icon name="users" /><span>Pelanggan</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><x-icon name="chart" /><span>Laporan</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" href="{{ route('admin.payments.index') }}"><x-icon name="payment" /><span>Pembayaran</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.delivery-zones.*') ? 'active' : '' }}" href="{{ route('admin.delivery-zones.index') }}"><x-icon name="truck" /><span>Zona Delivery</span></a>
    <a class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}"><x-icon name="settings" /><span>Pengaturan</span></a>
</nav>
