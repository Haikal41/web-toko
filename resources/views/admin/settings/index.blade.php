@extends('layouts.admin')

@section('title', 'Pengaturan Toko')
@section('crumb', 'Sistem / Pengaturan')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="admin-grid-2">
        <div class="card panel">
            <h3 style="font-size:1.02rem">Identitas toko</h3>
            <div class="mt-2">
                <div class="field">
                    <label class="label">Logo toko</label>
                    @if($settings['store_logo'] ?? null)
                        <img src="{{ asset('storage/'.$settings['store_logo']) }}" alt="Logo toko" style="width:96px;height:96px;object-fit:cover;border-radius:12px;border:1px solid var(--line)">
                    @endif
                    <input type="file" class="input mt-1" name="store_logo" accept="image/*">
                    <div class="help">Maks 2 MB. Logo dipakai di header toko.</div>
                </div>
                <div class="field">
                    <label class="label">Nama toko</label>
                    <input class="input" name="store_name" value="{{ old('store_name', $settings['store_name'] ?? 'Multiverse Aksesoris') }}" required>
                </div>
                <div class="field">
                    <label class="label">Tagline</label>
                    <input class="input" name="store_tagline" value="{{ old('store_tagline', $settings['store_tagline'] ?? '') }}">
                </div>
                <div class="field">
                    <label class="label">Deskripsi singkat</label>
                    <textarea class="textarea" name="store_description">{{ old('store_description', $settings['store_description'] ?? '') }}</textarea>
                </div>
                <div class="field">
                    <label class="label">Alamat toko</label>
                    <input class="input" name="store_address" value="{{ old('store_address', $settings['store_address'] ?? '') }}">
                </div>
                <div class="form-grid">
                    <div class="field">
                        <label class="label">Nomor WhatsApp</label>
                        <input class="input" name="store_phone" value="{{ old('store_phone', $settings['store_phone'] ?? '') }}" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="field">
                        <label class="label">Email</label>
                        <input type="email" class="input" name="store_email" value="{{ old('store_email', $settings['store_email'] ?? '') }}">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Nomor WhatsApp admin (notifikasi pesanan)</label>
                    <input class="input" name="wa_admin_number" value="{{ old('wa_admin_number', $settings['wa_admin_number'] ?? '') }}" placeholder="08xxxxxxxxxx">
                    <div class="help">Kosongkan untuk memakai nilai <code>WA_ADMIN_NUMBER</code> di file <code>.env</code>.</div>
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <h3 style="font-size:1.02rem">Pengiriman & stok</h3>
                <div class="field mt-2">
                    <label class="label">Ongkos kirim flat (Rp)</label>
                    <input type="number" class="input" name="shipping_flat" value="{{ old('shipping_flat', $settings['shipping_flat'] ?? 15000) }}" min="0" step="1000" required>
                    <div class="help">Dipakai untuk seluruh pesanan pada MVP.</div>
                </div>
                <div class="field">
                    <label class="label">Ambang stok menipis default</label>
                    <input type="number" class="input" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 5) }}" min="0" required>
                </div>
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Simpan</h3>
                <p class="tiny muted">Perubahan langsung diterapkan ke storefront.</p>
                <button class="btn btn-block mt-1" type="submit">Simpan pengaturan</button>
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Konfigurasi lanjutan (.env)</h3>
                <p class="tiny muted mb-0">Untuk pembayaran dan WhatsApp, isi variabel berikut di file <code>.env</code>:</p>
                <ul class="tiny muted" style="margin:.5rem 0 0 1rem">
                    <li><code>MIDTRANS_SERVER_KEY</code>, <code>MIDTRANS_CLIENT_KEY</code></li>
                    <li><code>WA_API_KEY</code>, <code>WA_ADMIN_NUMBER</code></li>
                    <li><code>QUEUE_CONNECTION</code> (jalankan <code>php artisan queue:work</code>)</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
