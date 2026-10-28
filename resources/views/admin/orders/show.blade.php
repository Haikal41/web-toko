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
    <div class="flex gap-sm" style="flex-wrap:wrap">
        <a href="{{ route('admin.orders.receipt', $order) }}" class="btn btn-outline btn-sm" target="_blank">Struk</a>
        @if($order->isDelivery())
            <a href="{{ route('admin.orders.shipping-label', ['order' => $order, 'auto' => 1]) }}" class="btn btn-outline btn-sm" target="_blank">Cetak resi</a>
        @endif
        @if($order->hasReceipt() && $order->customer_phone)
            <form action="{{ route('admin.orders.resend-receipt', $order) }}" method="POST" style="display:inline">
                @csrf
                <button class="btn btn-outline btn-sm" type="submit" data-confirm="Kirim ulang struk {{ $order->order_number }} ke WhatsApp pembeli?">Kirim ulang struk</button>
            </form>
        @endif
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">&larr; Semua pesanan</a>
    </div>
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
                        <tr><th>Penerima</th><th>Tipe</th><th>Jenis</th><th>Status</th><th>Percobaan</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @forelse($order->whatsappLogs as $log)
                            <tr>
                                <td class="small">{{ $log->recipient }}</td>
                                <td class="small">{{ ucfirst($log->recipient_type) }}</td>
                                <td class="small">{{ $log->messageTypeLabel() }}</td>
                                <td><span class="pill pill-{{ $log->status === 'sent' ? 'green' : ($log->status === 'failed' ? 'red' : 'amber') }}">{{ $log->status }}</span></td>
                                <td class="small">{{ $log->attempts }}</td>
                                <td class="small muted">{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center muted" style="padding:1.6rem">Belum ada log. Notifikasi dikirim setelah pembayaran sukses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="panel-head">
                <div>
                    <h3>Riwayat status</h3>
                    <p>Siapa mengubah status dan kapan</p>
                </div>
            </div>
            <div class="table-wrap" style="border:0;border-radius:0">
                <table class="table">
                    <thead>
                        <tr><th>Status</th><th>Oleh</th><th>Catatan</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @forelse($order->statusLogs as $log)
                            <tr>
                                <td><span class="pill pill-{{ \App\Models\Order::colorFor($log->to_status) }}">{{ \App\Models\Order::labelFor($log->to_status) }}</span></td>
                                <td class="small">{{ $log->user?->name ?? 'Sistem' }}</td>
                                <td class="small">{{ $log->note ?? '-' }}</td>
                                <td class="small muted nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center muted" style="padding:1.4rem">Belum ada perubahan status.</td></tr>
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
                        @foreach(array_diff(\App\Models\Order::STATUSES, ['batal']) as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="label">Catatan <span>(opsional)</span></label>
                    <input class="input" name="note" placeholder="Mis. sudah dikemas">
                </div>
                <button class="btn btn-block" type="submit">Simpan status</button>
            </form>
            <p class="help">Membatalkan pesanan yang sudah dibayar akan mengembalikan stok.</p>
        </div>

        @if($order->canBeCancelledBy(auth()->user()))
            <div class="card panel">
                <h3 style="font-size:1.02rem">Batalkan pesanan</h3>
                <p class="tiny muted">Stok otomatis dikembalikan bila pesanan sudah dibayar. Refund uang dicatat manual.</p>
                <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" class="mt-2">
                    @csrf
                    <div class="field">
                        <label class="label">Alasan pembatalan</label>
                        <textarea class="textarea" name="reason" required maxlength="500" placeholder="Mis. pembeli membatalkan via WhatsApp"></textarea>
                    </div>
                    <button class="btn btn-danger btn-block" type="submit" data-confirm="Batalkan pesanan {{ $order->order_number }}?">Batalkan pesanan</button>
                </form>
                @if(! auth()->user()->isOwner())
                    <p class="help">Kasir dapat membatalkan pesanan sampai status diproses.</p>
                @endif
            </div>
        @endif

        <div class="card panel">
            <h3 style="font-size:1.02rem">Pembeli</h3>
            <dl class="detail-list mt-2">
                <dt>Nama</dt><dd>{{ $order->customer_name }}</dd>
                <dt>WhatsApp</dt>
                <dd>
                    @if($order->customer_phone)
                        <a href="{{ wa_link($order->customer_phone) }}" target="_blank" class="bold" style="color:var(--green)">{{ $order->customer_phone }}</a>
                    @else
                        <span class="muted">-</span>
                    @endif
                </dd>
                <dt>Email</dt><dd class="small">{{ $order->user?->email ?? 'Tamu (tanpa akun)' }}</dd>
                <dt>Alamat</dt><dd class="small">{{ $order->customer_address ?: '-' }}</dd>
                <dt>Sumber</dt><dd class="small">{{ $order->channelLabel() }}</dd>
                <dt>Pemenuhan</dt><dd class="small">{{ $order->fulfillmentLabel() }}</dd>
                <dt>Kasir</dt><dd class="small">{{ $order->creator?->name ?? '-' }}</dd>
            </dl>
            @if($order->notes)
                <div class="alert alert-info small mt-2 mb-0">{{ $order->notes }}</div>
            @endif
            @if($order->status === 'batal' && $order->cancel_reason)
                <div class="alert alert-error small mt-2 mb-0">
                    Dibatalkan {{ $order->cancelled_at?->format('d M Y H:i') }} oleh {{ $order->canceller?->name ?? 'sistem' }}:<br>
                    {{ $order->cancel_reason }}
                </div>
            @endif
        </div>

        <div class="card panel">
            <h3 style="font-size:1.02rem">Pembayaran</h3>
            @if($order->status === 'pending')
                @php($payUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('checkout.payment', now()->addDays(7), ['orderNumber' => $order->order_number]))
                <div class="alert alert-info small">
                    Menunggu pembayaran. Kirim tautan ini ke pembeli:
                    <div class="tiny mt-1" style="word-break:break-all"><a href="{{ $payUrl }}" target="_blank">{{ $payUrl }}</a></div>
                </div>
                <a href="{{ wa_link($order->customer_phone, 'Silakan selesaikan pembayaran pesanan '.$order->order_number.' melalui tautan berikut: '.$payUrl) }}" target="_blank" rel="noopener" class="btn btn-outline btn-block mt-2">Kirim link via WhatsApp</a>
            @endif
            @forelse($order->payments as $payment)
                <dl class="detail-list mt-2" style="margin-bottom:1rem">
                    <dt>Status</dt><dd><span class="pill pill-{{ $payment->status === 'settlement' ? 'green' : ($payment->status === 'pending' ? 'amber' : 'red') }}">{{ $payment->status }}</span></dd>
                    <dt>Metode</dt><dd>{{ $payment->payment_type ?? '-' }}</dd>
                    <dt>ID transaksi</dt><dd class="tiny">{{ $payment->transaction_id ?? '-' }}</dd>
                    <dt>Dibayar</dt><dd class="small">{{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                    @if($order->cash_received !== null)
                        <dt>Tunai diterima</dt><dd class="small">{{ rupiah($order->cash_received) }}</dd>
                        <dt>Kembalian</dt><dd class="small">{{ rupiah($order->change_amount) }}</dd>
                    @endif
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
