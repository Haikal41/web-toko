@extends('layouts.app')

@section('title', 'Akun Saya — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><span>Akun</span></div>
        <h1>Halo, {{ $user->name }}</h1>
        <div class="flex gap-sm mt-1">
            @if($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm">Dashboard Admin</a>
            @endif
            @if($user->isKaryawan())
                <a href="{{ route('attendance.index') }}" class="btn btn-sm">Absensi Karyawan</a>
            @endif
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="grid" style="grid-template-columns:1.4fr 1fr;align-items:start">
        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card">
                <div class="panel-head">
                    <div>
                        <h3>Pesanan saya</h3>
                        <p>Semua transaksi yang pernah kamu buat.</p>
                    </div>
                </div>

                @if($orders->isEmpty())
                    <div class="empty-state" style="padding:2.5rem 1rem">
                        <div class="emoji">&#128230;</div>
                        <p>Belum ada pesanan. Yuk mulai belanja!</p>
                        <a href="{{ route('products.index') }}" class="btn">Lihat produk</a>
                    </div>
                @else
                    <div class="table-wrap" style="border:0;border-radius:0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Item</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="bold">{{ $order->order_number }}</td>
                                        <td class="small">{{ $order->created_at->format('d M Y') }}</td>
                                        <td class="small">{{ $order->items_count }} item</td>
                                        <td class="bold nowrap">{{ rupiah($order->total) }}</td>
                                        <td><span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                                        <td><a href="{{ route('account.order.show', $order->order_number) }}" class="btn btn-outline btn-sm">Detail</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel">{{ $orders->links() }}</div>
                @endif
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Profil</h3>
                <form action="{{ route('account.profile.update') }}" method="POST" class="mt-2">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <div class="field">
                            <label class="label">Nama</label>
                            <input class="input" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="field">
                            <label class="label">Email</label>
                            <input type="email" class="input" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="field">
                            <label class="label">Nomor WhatsApp</label>
                            <input class="input" name="phone" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="field">
                            <label class="label">Password baru <span>(opsional)</span></label>
                            <input type="password" class="input" name="password" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="field">
                            <label class="label">Konfirmasi password baru</label>
                            <input type="password" class="input" name="password_confirmation">
                        </div>
                    </div>
                    <button class="btn" type="submit">Simpan profil</button>
                </form>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <div class="flex-between">
                    <h3 style="font-size:1.02rem;margin:0">Alamat</h3>
                    <button class="btn btn-outline btn-sm" type="button" onclick="document.getElementById('new-address').classList.toggle('hidden')">+ Tambah</button>
                </div>

                @forelse($addresses as $address)
                    <div class="card" style="padding:.9rem;margin-top:.8rem">
                        <div class="flex-between">
                            <strong class="small">{{ $address->recipient_name }}</strong>
                            @if($address->is_default)<span class="pill pill-green">Utama</span>@endif
                        </div>
                        <div class="tiny muted">{{ $address->phone }}</div>
                        <div class="tiny muted">{{ $address->address }}, {{ $address->city }} {{ $address->postal_code }}</div>
                        <div class="flex gap-sm mt-1">
                            @unless($address->is_default)
                                <form action="{{ route('account.address.default', $address) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-ghost btn-sm" type="submit">Jadikan utama</button>
                                </form>
                            @endunless
                            <form action="{{ route('account.address.destroy', $address) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-ghost btn-sm" style="color:var(--red)" data-confirm="Hapus alamat ini?" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="small muted mt-2">Belum ada alamat tersimpan.</p>
                @endforelse

                <form id="new-address" action="{{ route('account.address.store') }}" method="POST" class="mt-2 hidden">
                    @csrf
                    <div class="field">
                        <label class="label">Nama penerima</label>
                        <input class="input" name="recipient_name" required>
                    </div>
                    <div class="field">
                        <label class="label">Nomor WhatsApp</label>
                        <input class="input" name="phone" required>
                    </div>
                    <div class="field">
                        <label class="label">Alamat lengkap</label>
                        <textarea class="textarea" name="address" required></textarea>
                    </div>
                    <div class="form-grid">
                        <div class="field">
                            <label class="label">Kota</label>
                            <input class="input" name="city" required>
                        </div>
                        <div class="field">
                            <label class="label">Kode pos</label>
                            <input class="input" name="postal_code">
                        </div>
                    </div>
                    <button class="btn btn-sm" type="submit">Simpan alamat</button>
                </form>
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Butuh bantuan?</h3>
                <p class="small muted">Hubungi admin untuk pertanyaan pesanan, custom, atau pengiriman.</p>
                <a href="{{ wa_link(store_phone(), 'Halo admin, saya butuh bantuan terkait pesanan saya.') }}" target="_blank" rel="noopener" class="btn btn-outline btn-block">Chat WhatsApp</a>
                <form action="{{ route('logout') }}" method="POST" class="mt-2">
                    @csrf
                    <button class="btn btn-ghost btn-block" style="color:var(--red)" type="submit">Keluar dari akun</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
