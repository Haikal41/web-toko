@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('crumb', 'Produk / ' . ($product->exists ? 'Edit' : 'Baru'))

@section('content')
<form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($product->exists)
        @method('PUT')
    @endif

    <div class="admin-grid-2">
        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <h3 style="font-size:1.02rem">Informasi produk</h3>
                <div class="mt-2">
                    <div class="field">
                        <label class="label">Nama produk</label>
                        <input class="input" name="name" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label">Kategori</label>
                            <select class="select" name="category_id" required>
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="label">Harga (Rp)</label>
                            <input type="number" class="input" name="price" value="{{ old('price', $product->price) }}" min="0" step="500" required>
                        </div>
                        <div class="field">
                            <label class="label">Stok</label>
                            <input type="number" class="input" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required>
                        </div>
                        <div class="field">
                            <label class="label">Batas stok menipis</label>
                            <input type="number" class="input" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" min="0" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Deskripsi</label>
                        <textarea class="textarea" name="description" rows="5" placeholder="Bahan, ukuran, dan detail produk">{{ old('description', $product->description) }}</textarea>
                    </div>
                    <div class="flex gap" style="flex-wrap:wrap">
                        <label class="checkbox-row">
                            <input type="checkbox" name="requires_personalization" value="1" @checked(old('requires_personalization', $product->requires_personalization))>
                            Wajib personalisasi (teks/ukiran)
                        </label>
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
                            Tampilkan di toko
                        </label>
                    </div>
                </div>
            </div>

            <div class="card panel">
                <div class="flex-between">
                    <div>
                        <h3 style="font-size:1.02rem">Varian</h3>
                        <p class="tiny muted" style="margin:0">Opsional. Contoh: Merah / 10 cm, Emas.</p>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addVariant()">+ Varian</button>
                </div>

                <div id="variant-rows" class="mt-2" style="display:flex;flex-direction:column;gap:.7rem">
                    @foreach($product->variants as $variant)
                        <div class="variant-row" style="display:grid;grid-template-columns:1.4fr .8fr .6fr auto;gap:.5rem;align-items:end">
                            <input type="hidden" name="variant_id[]" value="{{ $variant->id }}">
                            <div class="field" style="margin:0">
                                <label class="label tiny">Nama varian</label>
                                <input class="input" name="variant_name[]" value="{{ $variant->name }}">
                            </div>
                            <div class="field" style="margin:0">
                                <label class="label tiny">Tambahan harga</label>
                                <input type="number" class="input" name="variant_price[]" value="{{ (float) $variant->price_adjustment }}" min="0" step="500">
                            </div>
                            <div class="field" style="margin:0">
                                <label class="label tiny">Stok</label>
                                <input type="number" class="input" name="variant_stock[]" value="{{ $variant->stock }}" min="0">
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--red)" onclick="this.closest('.variant-row').remove()">&times;</button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <h3 style="font-size:1.02rem">Foto produk</h3>
                <p class="tiny muted">Upload beberapa foto. Foto pertama menjadi foto utama. Maks 4 MB per file.</p>
                <input type="file" class="input mt-1" name="images[]" accept="image/*" multiple>

                @if($product->exists && $product->images->isNotEmpty())
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.7rem;margin-top:1rem">
                        @foreach($product->images as $image)
                            <div style="position:relative">
                                <img src="{{ asset('storage/'.$image->path) }}" alt="" style="border-radius:10px;aspect-ratio:1;object-fit:cover;width:100%">
                                @if($image->is_primary)
                                    <span class="pill pill-green" style="position:absolute;top:.3rem;left:.3rem">Utama</span>
                                @else
                                    <button type="button" class="btn btn-outline btn-sm" style="position:absolute;top:.3rem;left:.3rem" onclick="document.getElementById('primary-image-{{ $image->id }}').submit()">Jadikan utama</button>
                                @endif
                                <button type="button" class="btn btn-danger btn-sm" style="position:absolute;top:.3rem;right:.3rem" onclick="if(confirm('Hapus foto ini?')) document.getElementById('delete-image-{{ $image->id }}').submit()">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Aksi</h3>
                <button type="submit" class="btn btn-block mt-1">{{ $product->exists ? 'Simpan perubahan' : 'Tambah produk' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-block mt-1">Batal</a>
            </div>
        </div>
    </div>
</form>

@if($product->exists)
    @foreach($product->images as $image)
        <form id="delete-image-{{ $image->id }}" action="{{ route('admin.products.image.destroy', [$product, $image]) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        <form id="primary-image-{{ $image->id }}" action="{{ route('admin.products.image.primary', [$product, $image]) }}" method="POST" class="hidden">
            @csrf
            @method('PATCH')
        </form>
    @endforeach
@endif
@endsection

@push('scripts')
<script>
    function addVariant() {
        const container = document.getElementById('variant-rows');
        const row = document.createElement('div');
        row.className = 'variant-row';
        row.style.cssText = 'display:grid;grid-template-columns:1.4fr .8fr .6fr auto;gap:.5rem;align-items:end';
        row.innerHTML = `
            <div class="field" style="margin:0">
                <label class="label tiny">Nama varian</label>
                <input class="input" name="variant_name[]" placeholder="Mis. Merah / 10 cm">
            </div>
            <div class="field" style="margin:0">
                <label class="label tiny">Tambahan harga</label>
                <input type="number" class="input" name="variant_price[]" value="0" min="0" step="500">
            </div>
            <div class="field" style="margin:0">
                <label class="label tiny">Stok</label>
                <input type="number" class="input" name="variant_stock[]" value="0" min="0">
            </div>
            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--red)" onclick="this.closest('.variant-row').remove()">&times;</button>
        `;
        container.appendChild(row);
    }
</script>
@endpush
