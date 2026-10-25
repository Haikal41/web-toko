@extends('layouts.app')

@section('title', 'Katalog Produk — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a>
            <span>/</span>
            <span>Produk</span>
        </div>
        <h1>Koleksi aksesoris</h1>
        <p class="muted" style="max-width:60ch">Temukan gantungan kunci, boneka, pigura, dan piala yang bisa dipersonalisasi untuk hadiah spesialmu.</p>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <form method="GET" action="{{ route('products.index') }}" class="card filter-bar">
        <div class="field search">
            <label class="label" for="q">Cari produk</label>
            <input id="q" type="search" name="q" class="input" placeholder="Mis. piala ukir, boneka beruang..." value="{{ request('q') }}">
        </div>
        <div class="field">
            <label class="label" for="category">Kategori</label>
            <select id="category" name="category" class="select">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label class="label" for="sort">Urutkan</label>
            <select id="sort" name="sort" class="select">
                <option value="">Terbaru</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Harga terendah</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Harga tertinggi</option>
                <option value="popular" @selected(request('sort') === 'popular')>Paling laris</option>
            </select>
        </div>
        <div class="field">
            <label class="label" for="min_price">Harga min</label>
            <input id="min_price" type="number" name="min_price" class="input" placeholder="0" value="{{ request('min_price') }}" min="0">
        </div>
        <div class="field">
            <label class="label" for="max_price">Harga max</label>
            <input id="max_price" type="number" name="max_price" class="input" placeholder="500000" value="{{ request('max_price') }}" min="0">
        </div>
        <button class="btn" type="submit">Terapkan</button>
        @if(request()->hasAny(['q', 'category', 'sort', 'min_price', 'max_price']))
            <a href="{{ route('products.index') }}" class="btn btn-ghost">Reset</a>
        @endif
    </form>

    <div class="flex-between mb-2">
        <span class="small muted">Menampilkan {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk</span>
    </div>

    @if($products->isEmpty())
        <div class="card empty-state">
            <div class="emoji">&#128269;</div>
            <h3>Produk tidak ditemukan</h3>
            <p>Coba kata kunci lain atau lihat semua koleksi kami.</p>
            <a href="{{ route('products.index') }}" class="btn">Lihat semua produk</a>
        </div>
    @else
        <div class="grid grid-4">
            @foreach($products as $product)
                @include('partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="pagination">
            @if($products->onFirstPage())
                <span class="disabled">&laquo;</span>
            @else
                <a href="{{ $products->previousPageUrl() }}">&laquo;</a>
            @endif

            @foreach($products->links()->elements[0] as $page => $url)
                @if($page == $products->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}">&raquo;</a>
            @else
                <span class="disabled">&raquo;</span>
            @endif
        </div>
    @endif
</div>
@endsection
