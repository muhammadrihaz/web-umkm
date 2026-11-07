@extends('layouts.app')
@section('title', 'Checkout · '.config('app.name'))
@section('content')
@php($subtotal = $cart->items->sum(fn ($item) => $item->product->price * $item->quantity))
<div class="content-page">
    <div class="checkout-steps" aria-label="Tahapan checkout">
        <div class="checkout-step active"><span class="step-number">✓</span><span>Keranjang</span></div>
        <div class="checkout-step active"><span class="step-number">2</span><span>Pengiriman</span></div>
        <div class="checkout-step"><span class="step-number">3</span><span>Pembayaran</span></div>
        <div class="checkout-step"><span class="step-number">4</span><span>Konfirmasi</span></div>
    </div>
    <form method="post" action="{{ route('checkout.store') }}">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
        <div class="checkout-layout">
            <section class="checkout-panel" aria-labelledby="cart-summary-heading">
                <div class="d-flex align-items-center justify-content-between"><h2 id="cart-summary-heading" class="mb-0">Keranjang Belanja ({{ $cart->items->sum('quantity') }} Produk)</h2><a class="small fw-bold" href="{{ route('cart.index') }}">Ubah</a></div>
                @foreach($cart->items as $item)
                    <div class="checkout-item">
                        <div class="checkout-thumb"><x-product-visual :product="$item->product" /></div>
                        <div><strong>{{ $item->product->name }}</strong><small>{{ $item->quantity }} × Rp {{ number_format($item->product->price, 0, ',', '.') }}</small></div>
                        <span class="checkout-item-price">Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="checkout-subsection">
                    <h3>Alamat Penerima</h3>
                    <div class="row g-2">
                        <div class="col-sm-6"><label class="form-label" for="recipient-name">Nama penerima</label><input id="recipient-name" class="form-control" name="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required></div>
                        <div class="col-sm-6"><label class="form-label" for="recipient-phone">Telepon</label><input id="recipient-phone" class="form-control" name="recipient_phone" value="{{ old('recipient_phone', auth()->user()->phone) }}" required></div>
                        <div class="col-12 delivery-field"><label class="form-label" for="delivery-address">Alamat lengkap</label><textarea id="delivery-address" class="form-control" name="delivery_address" rows="2" placeholder="Jalan, banjar/desa, kecamatan, dan patokan">{{ old('delivery_address') }}</textarea></div>
                    </div>
                </div>
            </section>

            <section class="checkout-panel" aria-labelledby="delivery-method-heading">
                <h2 id="delivery-method-heading">Metode Pengambilan</h2>
                <label class="method-card">
                    <input type="radio" name="fulfillment_method" value="pickup" @checked(old('fulfillment_method', 'pickup') === 'pickup')>
                    <x-icon name="pickup" size="28" />
                    <span><strong>Pickup di Toko (Gratis)</strong><small>Ambil pesanan langsung di lokasi UMKM Pangkonan.</small></span>
                </label>
                <label class="method-card">
                    <input type="radio" name="fulfillment_method" value="delivery" @checked(old('fulfillment_method') === 'delivery')>
                    <x-icon name="truck" size="28" />
                    <span><strong>Delivery Lokal</strong><small>Diantar ke alamat di area yang tersedia.</small></span>
                </label>
                <div class="checkout-subsection delivery-field">
                    <h3>Zona Ongkir</h3>
                    <label class="visually-hidden" for="delivery-zone">Pilih zona ongkir</label>
                    <select id="delivery-zone" class="form-select" name="delivery_zone_id">
                        <option value="" data-fee="0">Pilih zona delivery</option>
                        @foreach($zones as $zone)<option value="{{ $zone->id }}" data-fee="{{ $zone->fee }}" @selected((string) old('delivery_zone_id') === (string) $zone->id)>{{ $zone->name }} (Rp {{ number_format($zone->fee, 0, ',', '.') }})</option>@endforeach
                    </select>
                </div>
                <div class="checkout-subsection">
                    <h3>Catatan Pesanan (Opsional)</h3>
                    <label class="visually-hidden" for="customer-notes">Catatan pesanan</label>
                    <textarea id="customer-notes" class="form-control" name="customer_notes" rows="3" placeholder="Contoh: Mohon dikemas rapi untuk oleh-oleh.">{{ old('customer_notes') }}</textarea>
                </div>
            </section>

            <aside class="checkout-panel checkout-summary-panel" aria-labelledby="payment-summary-heading">
                <h2 id="payment-summary-heading">Ringkasan Pesanan</h2>
                <div class="summary-row"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                <div class="summary-row"><span>Ongkir</span><span id="delivery-fee">Rp 0</span></div>
                <div class="summary-row total"><span>Total Pembayaran</span><strong id="checkout-total">Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                <div class="checkout-subsection">
                    <h3>Metode Pembayaran</h3>
                    <label class="method-card">
                        <input type="radio" name="payment_method" value="midtrans" @checked(old('payment_method', 'midtrans') === 'midtrans')>
                        <x-icon name="payment" size="27" />
                        <span><strong>Midtrans Snap</strong><small>Bayar online melalui kanal yang tersedia.</small></span>
                    </label>
                    @if(config('pangkonan.cod_enabled'))
                        <label class="method-card">
                            <input type="radio" name="payment_method" value="cod" @checked(old('payment_method') === 'cod')>
                            <x-icon name="orders" size="27" />
                            <span><strong>COD (Bayar di Tempat)</strong><small>Bayar saat pesanan diterima.</small></span>
                        </label>
                    @endif
                </div>
                <button class="btn btn-brand w-100 mt-3"><x-icon name="lock" size="16" /> Lanjut ke Pembayaran</button>
                <div class="secure-note"><x-icon name="shield" size="15" /><span>Harga dan stok dihitung ulang oleh server. Data pembayaran diproses melalui kanal yang dipilih.</span></div>
            </aside>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    const fulfillmentInputs = document.querySelectorAll('input[name="fulfillment_method"]');
    const deliveryFields = document.querySelectorAll('.delivery-field');
    const deliveryZone = document.getElementById('delivery-zone');
    const deliveryAddress = document.getElementById('delivery-address');
    const deliveryFee = document.getElementById('delivery-fee');
    const checkoutTotal = document.getElementById('checkout-total');
    const subtotal = {{ $subtotal }};
    const rupiah = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
    function updateCheckout() {
        const isDelivery = document.querySelector('input[name="fulfillment_method"]:checked')?.value === 'delivery';
        deliveryFields.forEach(field => field.classList.toggle('d-none', !isDelivery));
        deliveryZone.required = isDelivery;
        deliveryAddress.required = isDelivery;
        const fee = isDelivery ? Number(deliveryZone.selectedOptions[0]?.dataset.fee ?? 0) : 0;
        deliveryFee.textContent = rupiah(fee);
        checkoutTotal.textContent = rupiah(subtotal + fee);
    }
    fulfillmentInputs.forEach(input => input.addEventListener('change', updateCheckout));
    deliveryZone.addEventListener('change', updateCheckout);
    updateCheckout();
</script>
@endpush
