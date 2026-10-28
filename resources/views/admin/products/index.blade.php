@extends('layouts.admin')

@section('title', 'Produk')
@section('crumb', 'Katalog produk')

@section('content')
<div class="card">
    <div class="panel-head">
        <div>
            <h3>Daftar produk</h3>
            <p>{{ $products->total() }} produk terdaftar</p>
        </div>
        @if(auth()->user()->isOwner())
            <a href="{{ route('admin.products.create') }}" class="btn">+ Tambah produk</a>
        @endif
    </div>

    <div class="panel-body" style="padding-bottom:.6rem">
        <form method="GET" class="toolbar">
            <div class="field">
                <label class="label">Cari</label>
                <input class="input" name="q" value="{{ request('q') }}" placeholder="Nama produk">
            </div>
            <div class="field">
                <label class="label">Kategori</label>
                <select class="select" name="category">
                    <option value="">Semua</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline">Filter</button>
            @if(request()->hasAny(['q', 'category']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Custom</th>
                    <th>Status</th>
                    <th>Varian</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="mini-product">
                                <img class="mini-thumb" src="{{ $product->imageUrl() }}" alt="">
                                <div>
                                    <div class="bold small">{{ $product->name }}</div>
                                    <div class="tiny muted">{{ $product->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="small">{{ $product->category?->name ?? '-' }}</td>
                        <td class="bold nowrap">{{ rupiah($product->price) }}</td>
                        <td>
                            @if($product->stock <= 0)
                                <span class="pill pill-red">Habis</span>
                            @elseif($product->stock <= $product->low_stock_threshold)
                                <span class="pill pill-amber">{{ $product->stock }} (tipis)</span>
                            @else
                                <span class="pill pill-green">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>{!! $product->requires_personalization ? '<span class="pill pill-indigo">Wajib</span>' : '<span class="tiny muted">-</span>' !!}</td>
                        <td><span class="pill pill-{{ $product->is_active ? 'green' : 'gray' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="small">{{ $product->variants_count }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline btn-sm">Lihat</a>
                                @if(auth()->user()->isOwner())
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline btn-sm">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit" data-confirm="Hapus produk {{ $product->name }}?">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center muted" style="padding:2.5rem">Belum ada produk. Tambahkan produk pertama Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">{{ $products->links() }}</div>
</div>
@endsection
