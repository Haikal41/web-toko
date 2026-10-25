@extends('layouts.app')

@section('title', $product->name.' — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a>
            <span>/</span>
            <a href="{{ route('products.index') }}">Produk</a>
            <span>/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug ?? '']) }}">{{ $product->category->name ?? 'Kategori' }}</a>
            <span>/</span>
            <span>{{ $product->name }}</span>
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="product-detail">
        <div>
            @php($images = $product->images)
            <div class="gallery-main">
                <img data-gallery-main src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
            </div>
            @if($images->count() > 1)
                <div class="gallery-thumbs">
                    @foreach($images as $image)
                        <button type="button" data-gallery-thumb data-src="{{ asset('storage/'.$image->path) }}" class="{{ $loop->first ? 'active' : '' }}">
                            <img src="{{ asset('storage/'.$image->path) }}" alt="Foto {{ $loop->iteration }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <span class="eyebrow">{{ $product->category->name ?? 'Aksesoris' }}</span>
            <h1 class="display" style="font-size:2.2rem;margin:.7rem 0 .4rem">{{ $product->name }}</h1>
            <div class="stars mb-2">&#9733;&#9733;&#9733;&#9733;&#9733; <span class="muted small">(siap dipersonalisasi)</span></div>
            <div class="detail-price">{{ rupiah($product->price) }}</div>
            <div class="detail-stock">
                @if($product->stock > 0)
                    <span class="pill pill-green">Tersedia</span>
                    <span style="margin-left:.4rem">Stok: {{ $product->stock }}</span>
                @else
                    <span class="pill pill-red">Stok habis</span>
                @endif
            </div>

            <p class="muted">{{ $product->description ?: 'Aksesoris berkualitas yang dibuat dengan detail. Hubungi kami untuk informasi bahan dan ukuran.' }}</p>

            <form action="{{ route('cart.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($product->variants->isNotEmpty())
                    <div class="field">
                        <label class="label">Pilih varian</label>
                        <div class="option-row">
                            @foreach($product->variants as $variant)
                                <label class="option-pill">
                                    <input type="radio" name="variant_id" value="{{ $variant->id }}" @checked($loop->first)>
                                    <span>
                                        {{ $variant->name }}
                                        @if((float) $variant->price_adjustment > 0)
                                            <small>(+{{ rupiah($variant->price_adjustment) }})</small>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($product->requires_personalization)
                    <div class="persona-box">
                        <h4>&#9998; Personalisasi</h4>
                        <div class="help">Produk ini wajib dipersonalisasi. Tulis nama/ucapan yang ingin diukir.</div>
                        <div class="field">
                            <label class="label" for="personalization_text">Teks personalisasi <span>(maks. 60 karakter)</span></label>
                            <input id="personalization_text" name="personalization_text" class="input @error('personalization_text') is-invalid @enderror"
                                   maxlength="60" placeholder="Contoh: Untuk Rina — Wisuda 2026" value="{{ old('personalization_text') }}">
                            @error('personalization_text') <div class="error-text">{{ $message }}</div> @enderror
                        </div>
                        <div class="field" style="margin-bottom:0">
                            <label class="label" for="personalization_notes">Catatan tambahan <span>(opsional)</span></label>
                            <textarea id="personalization_notes" name="personalization_notes" class="textarea" maxlength="200"
                                      placeholder="Warna, font, atau detail lain yang diinginkan">{{ old('personalization_notes') }}</textarea>
                        </div>
                    </div>
                @endif

                <div class="field">
                    <label class="label" for="quantity">Jumlah</label>
                    <div class="flex gap-sm" style="align-items:center;flex-wrap:wrap">
                        <div class="qty-control" data-qty>
                            <button type="button" data-qty-minus aria-label="Kurangi">&minus;</button>
                            <input id="quantity" type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ max(1, $product->stock) }}">
                            <button type="button" data-qty-plus aria-label="Tambah">+</button>
                        </div>
                        <button class="btn btn-lg" type="submit" @disabled($product->stock <= 0)>
                            &#128717; Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </form>

            <div class="divider"></div>

            <div class="grid grid-3" style="gap:.7rem">
                <div class="hero-note">&#128230; Packing aman</div>
                <div class="hero-note">&#9998; Custom nama</div>
                <div class="hero-note">&#128172; Notif WhatsApp</div>
            </div>

            <div class="mt-3 small muted">
                Butuh bantuan memilih? <a href="{{ wa_link(store_phone(), 'Halo, saya ingin bertanya tentang produk '.$product->name.'.') }}" target="_blank" rel="noopener" style="color:var(--primary);font-weight:700">Chat admin via WhatsApp</a>
            </div>
        </div>
    </div>

    @if($related->isNotEmpty())
        <div class="section">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Rekomendasi</span>
                    <h2>Produk serupa</h2>
                </div>
            </div>
            <div class="grid grid-4">
                @foreach($related as $item)
                    @include('partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
