<article class="product-card">
    <div class="product-media">
        <x-product-visual :product="$product" />
        <span class="product-ribbon">Unggulan</span>
        <span class="favorite-mark" aria-hidden="true">♡</span>
    </div>
    <div class="product-card-body">
        <h3>{{ $product->name }}</h3>
        <p class="product-description">{{ $product->description ?: 'Camilan khas Bali dengan cita rasa autentik dari UMKM lokal Gianyar.' }}</p>
        <div class="product-meta">
            <span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            <span class="product-stock">Stok: {{ $product->available_stock }}</span>
        </div>
        <div class="product-actions">
            <a class="btn btn-outline-brand" href="{{ route('catalog.show', $product) }}">Lihat Detail</a>
            @auth
                @unless(auth()->user()->isAdmin())
                    <form method="post" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button class="btn btn-brand" @disabled($product->available_stock < 1)><x-icon name="plus" size="15" /> Tambah</button>
                    </form>
                @else
                    <a class="btn btn-brand" href="{{ route('admin.products.edit', $product) }}">Kelola</a>
                @endunless
            @else
                <a class="btn btn-brand" href="{{ route('login') }}"><x-icon name="plus" size="15" /> Tambah</a>
            @endauth
        </div>
    </div>
</article>
