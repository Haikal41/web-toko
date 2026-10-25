@extends('layouts.admin')

@section('title', 'Kupon')
@section('crumb', 'Laporan / Kupon')

@section('content')
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Daftar kupon</h3>
                <p>{{ $coupons->total() }} kupon</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead>
                    <tr><th>Kode</th><th>Nilai</th><th>Min. belanja</th><th>Dipakai</th><th>Berlaku</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td class="bold">{{ $coupon->code }}</td>
                            <td>
                                {{ $coupon->type === 'percentage' ? (float) $coupon->value.'%' : rupiah($coupon->value) }}
                            </td>
                            <td class="small">{{ rupiah($coupon->min_purchase) }}</td>
                            <td class="small">{{ $coupon->used_count }}{{ $coupon->max_uses ? '/'.$coupon->max_uses : '' }}</td>
                            <td class="tiny muted">
                                {{ $coupon->starts_at?->format('d M Y') ?? '—' }} s/d {{ $coupon->expires_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td><span class="pill pill-{{ $coupon->is_active ? 'green' : 'gray' }}">{{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="actions">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('coupon-{{ $coupon->id }}').showModal()">Edit</button>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-ghost btn-sm" style="color:var(--red)" type="submit" data-confirm="Hapus kupon {{ $coupon->code }}?">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <dialog id="coupon-{{ $coupon->id }}" style="border:0;border-radius:16px;padding:0;max-width:480px;width:92%;box-shadow:var(--shadow)">
                            <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="panel">
                                @csrf
                                @method('PUT')
                                <h3 style="font-size:1.05rem">Edit kupon</h3>
                                <div class="form-grid">
                                    <div class="field">
                                        <label class="label">Kode</label>
                                        <input class="input" name="code" value="{{ $coupon->code }}" required>
                                    </div>
                                    <div class="field">
                                        <label class="label">Tipe</label>
                                        <select class="select" name="type">
                                            <option value="percentage" @selected($coupon->type === 'percentage')>Persentase (%)</option>
                                            <option value="fixed" @selected($coupon->type === 'fixed')>Nominal (Rp)</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="label">Nilai</label>
                                        <input type="number" class="input" name="value" value="{{ (float) $coupon->value }}" min="0" step="500" required>
                                    </div>
                                    <div class="field">
                                        <label class="label">Min. belanja</label>
                                        <input type="number" class="input" name="min_purchase" value="{{ (float) $coupon->min_purchase }}" min="0" step="500">
                                    </div>
                                    <div class="field">
                                        <label class="label">Maks. pemakaian</label>
                                        <input type="number" class="input" name="max_uses" value="{{ $coupon->max_uses }}" min="1" placeholder="Tanpa batas">
                                    </div>
                                    <div class="field">
                                        <label class="label">Mulai</label>
                                        <input type="date" class="input" name="starts_at" value="{{ $coupon->starts_at?->format('Y-m-d') }}">
                                    </div>
                                    <div class="field">
                                        <label class="label">Berakhir</label>
                                        <input type="date" class="input" name="expires_at" value="{{ $coupon->expires_at?->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <label class="checkbox-row mb-2"><input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)> Aktif</label>
                                <div class="flex gap-sm">
                                    <button class="btn" type="submit">Simpan</button>
                                    <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                                </div>
                            </form>
                        </dialog>
                    @empty
                        <tr><td colspan="7" class="text-center muted" style="padding:2.5rem">Belum ada kupon.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel">{{ $coupons->links() }}</div>
    </div>

    <div class="card panel" style="align-self:start">
        <h3 style="font-size:1.02rem">Tambah kupon</h3>
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="mt-2">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label class="label">Kode</label>
                    <input class="input" name="code" placeholder="HADIAH10" required>
                </div>
                <div class="field">
                    <label class="label">Tipe</label>
                    <select class="select" name="type">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label">Nilai</label>
                    <input type="number" class="input" name="value" min="0" step="500" required>
                </div>
                <div class="field">
                    <label class="label">Min. belanja</label>
                    <input type="number" class="input" name="min_purchase" value="0" min="0" step="500">
                </div>
                <div class="field">
                    <label class="label">Maks. pemakaian</label>
                    <input type="number" class="input" name="max_uses" min="1" placeholder="Tanpa batas">
                </div>
                <div class="field">
                    <label class="label">Mulai</label>
                    <input type="date" class="input" name="starts_at">
                </div>
                <div class="field">
                    <label class="label">Berakhir</label>
                    <input type="date" class="input" name="expires_at">
                </div>
            </div>
            <label class="checkbox-row mb-2"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>
            <button class="btn btn-block" type="submit">Tambah kupon</button>
        </form>
    </div>
</div>
@endsection
