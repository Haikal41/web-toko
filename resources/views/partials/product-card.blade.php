<a href="{{ route('products.show', $product->slug) }}" class="product-card">
    <div class="product-thumb">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy">
        @if($product->stock <= 0)
            <span class="product-badge habis">Habis</span>
        @elseif($product->requires_personalization)
            <span class="product-badge personal">Bisa Custom</span>
        @endif
    </div>
    <div class="product-body">
        <span class="product-cat">{{ $product->category->name ?? 'Aksesoris' }}</span>
        <span class="product-name">{{ $product->name }}</span>
        <div class="product-meta">
            <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <span>Stok {{ $product->stock }}</span>
        </div>
        <span class="product-price">{{ rupiah($product->price) }}</span>
    </div>
</a>
