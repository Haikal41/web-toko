@extends('layouts.admin')

@section('title', 'Pesanan '.$order->order_number)
@section('crumb', 'Pesanan / Detail')

@section('content')
<div class="flex-between flex-wrap">
    <div>
        <div class="flex gap-sm" style="align-items:center">
            <h2 style="margin:0">{{ $order->order_number }}</h2>
            <span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
        </div>
        <p class="small muted" style="margin:.3rem 0 0">Dibuat {{ $order->created_at->translatedFormat('d F Y H:i') }}</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">&larr; Semua pesanan</a>
</div>

<div class="admin-grid-2">
    <div style="display:flex;flex-direction:column;gap:1.2rem">
        <div class="card">
            <div class="panel-head">
                <div>
                    <h3>Item pesanan</h3>
                    <p>Termasuk detail personalisasi</p>
                </div>
            </div>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr><th>Produk</th><th>Varian</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="bold small">{{ $item->product_name }}</div>
                                    @if($item->personalization_text)
                                        <div class="tiny" style="color:var(--primary);margin-top:.2rem">&#9998; {{ $item->personalization_text }}</div>
                                    @endif
                                    @if($item->personalization_notes)
                                        <div class="tiny muted">Catatan: {{ $item->personalization_notes }}</div>
                                    @endif
                                </td>
                                <td class="small">{{ $item->variant_name ?? '-' }}</td>
                                <td class="small nowrap">{{ rupiah($item->price) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="bold nowrap">{{ rupiah($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="panel">
                <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
                @if((float) $order->discount > 0)
                    <div class="summary-row"><span class="muted">Diskon</span><span>-{{ rupiah($order->discount) }}</span></div>
                @endif
                <div class="summary-row"><span class="muted">Ongkir</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
                <div class="summary-row total"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="panel-head">
                <div>
                    <h3>Log WhatsApp</h3>
                    <p>Riwayat notifikasi otomatis</p>
                </div>
            </div>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr><th>Penerima</th><th>Tipe</th><th>Status</th><th>Percobaan</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @forelse($order->whatsappLogs as $log)
                            <tr>
                                <td class="small">{{ $log->recipient }}</td>
                                <td class="small">{{ ucfirst($log->recipient_type) }}</td>
                                <td><span class="pill pill-{{ $log->status === 'sent' ? 'green' : ($log->status === 'failed' ? 'red' : 'amber') }}">{{ $log->status }}</span></td>
                                <td class="small">{{ $log->attempts }}</td>
                                <td class="small muted">{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center muted" style="padding:1.6rem">Belum ada log. Notifikasi dikirim setelah pembayaran sukses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.2rem">
        <div class="card panel">
            <h3 style="font-size:1.02rem">Ubah status</h3>
            <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-2">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label class="label">Status pesanan</label>
                    <select class="select" name="status">
                        @foreach(\App\Models\Order::STATUSES as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-block" type="submit">Simpan status</button>
            </form>
            <p class="help">Membatalkan pesanan yang sudah dibayar akan mengembalikan stok.</p>
        </div>

        <div class="card panel">
            <h3 style="font-size:1.02rem">Pembeli</h3>
            <dl class="detail-list mt-2">
                <dt>Nama</dt><dd>{{ $order->customer_name }}</dd>
                <dt>WhatsApp</dt><dd><a href="{{ wa_link($order->customer_phone) }}" target="_blank" class="bold" style="color:var(--green)">{{ $order->customer_phone }}</a></dd>
                <dt>Email</dt><dd class="small">{{ $order->user?->email ?? '-' }}</dd>
                <dt>Alamat</dt><dd class="small">{{ $order->customer_address }}</dd>
            </dl>
            @if($order->notes)
                <div class="alert alert-info small mt-2 mb-0">{{ $order->notes }}</div>
            @endif
        </div>

        <div class="card panel">
            <h3 style="font-size:1.02rem">Pembayaran</h3>
            @forelse($order->payments as $payment)
                <dl class="detail-list mt-2" style="margin-bottom:1rem">
                    <dt>Status</dt><dd><span class="pill pill-{{ $payment->status === 'settlement' ? 'green' : ($payment->status === 'pending' ? 'amber' : 'red') }}">{{ $payment->status }}</span></dd>
                    <dt>Metode</dt><dd>{{ $payment->payment_type ?? '-' }}</dd>
                    <dt>ID transaksi</dt><dd class="tiny">{{ $payment->transaction_id ?? '-' }}</dd>
                    <dt>Dibayar</dt><dd class="small">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                </dl>
            @empty
                <p class="small muted">Belum ada data pembayaran.</p>
            @endforelse
        </div>

        <div class="card panel">
            <h3 style="font-size:1.02rem">Aktivitas</h3>
            <dl class="detail-list mt-2">
                <dt>Dibuat</dt><dd class="small">{{ $order->created_at->format('d M Y H:i') }}</dd>
                <dt>Dibayar</dt><dd class="small">{{ $order->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                <dt>Update</dt><dd class="small">{{ $order->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
