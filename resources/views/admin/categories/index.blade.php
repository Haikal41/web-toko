@extends('layouts.admin')

@section('title', 'Kategori')
@section('crumb', 'Produk / Kategori')

@section('content')
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Daftar kategori</h3>
                <p>{{ $categories->count() }} kategori</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>Slug</th><th>Produk</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>
                                <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="flex gap-sm" style="align-items:center">
                                    @csrf
                                    @method('PUT')
                                    <input class="input" name="name" value="{{ $category->name }}" style="max-width:220px">
                                    <label class="checkbox-row tiny"><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Aktif</label>
                                    <button class="btn btn-outline btn-sm" type="submit">Simpan</button>
                                </form>
                            </td>
                            <td class="tiny muted">{{ $category->slug }}</td>
                            <td><span class="pill pill-gray">{{ $category->products_count }}</span></td>
                            <td><span class="pill pill-{{ $category->is_active ? 'green' : 'gray' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" style="color:var(--red)" type="submit" data-confirm="Hapus kategori {{ $category->name }}?">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card panel" style="align-self:start">
        <h3 style="font-size:1.02rem">Tambah kategori</h3>
        <p class="tiny muted">Kategori membantu pembeli menemukan produk lebih cepat.</p>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-2">
            @csrf
            <div class="field">
                <label class="label">Nama kategori</label>
                <input class="input" name="name" placeholder="Mis. Topi" required>
            </div>
            <button class="btn btn-block" type="submit">Tambah kategori</button>
        </form>
    </div>
</div>
@endsection
