@extends('layouts.admin')

@section('title', 'WhatsApp')
@section('crumb', 'Sistem / WhatsApp')

@section('content')
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Status integrasi</h3>
                <p>Notifikasi otomatis setelah pembayaran sukses</p>
            </div>
            <span class="pill pill-{{ filled(config('whatsapp.api_key')) ? 'green' : 'amber' }}">
                {{ filled(config('whatsapp.api_key')) ? 'Terkonfigurasi' : 'Mode simulasi' }}
            </span>
        </div>
        <div class="panel">
            <dl class="detail-list">
                <dt>Provider</dt><dd>{{ config('whatsapp.provider') }}</dd>
                <dt>API key</dt><dd>{{ filled(config('whatsapp.api_key')) ? 'Sudah diisi' : 'Belum diisi' }}</dd>
                <dt>Nomor admin</dt><dd>{{ config('whatsapp.admin_number') ?: 'Belum diisi' }}</dd>
                <dt>Template</dt><dd class="small">Pembeli & admin, dikirim saat status pesanan menjadi <em>paid</em>; berisi ringkasan dan tautan struk. Kirim ulang tersedia di detail pesanan.</dd>
            </dl>

            @if(blank(config('whatsapp.api_key')))
                <div class="alert alert-info small mt-2">
                    <strong>Mode simulasi aktif.</strong> Pesan tidak benar-benar dikirim, tetapi dicatat ke log aplikasi dan tabel <code>whatsapp_logs</code>. Isi <code>WA_API_KEY</code> dan <code>WA_ADMIN_NUMBER</code> di <code>.env</code> untuk pengiriman nyata via Fonnte.
                </div>
            @endif
        </div>
    </div>

    <div class="card panel" style="align-self:start">
        <h3 style="font-size:1.02rem">Tes kirim</h3>
        <p class="tiny muted">Kirim pesan uji ke nomor WhatsApp mana pun.</p>
        <form action="{{ route('admin.whatsapp.test') }}" method="POST" class="mt-2">
            @csrf
            <div class="field">
                <label class="label">Nomor tujuan</label>
                <input class="input" name="number" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="field">
                <label class="label">Pesan</label>
                <textarea class="textarea" name="message" required>Halo! Ini pesan uji dari {{ store_name() }}.</textarea>
            </div>
            <button class="btn btn-block" type="submit">Kirim pesan</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="panel-head">
        <div>
            <h3>Log notifikasi</h3>
            <p>{{ $logs->total() }} pengiriman tercatat</p>
        </div>
    </div>
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr><th>Waktu</th><th>Penerima</th><th>Tipe</th><th>Jenis</th><th>Pesanan</th><th>Status</th><th>Percobaan</th><th>Respons provider</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small muted nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="small">{{ $log->recipient }}</td>
                        <td class="small">{{ ucfirst($log->recipient_type) }}</td>
                        <td class="small">{{ $log->messageTypeLabel() }}</td>
                        <td class="small">{{ $log->order?->order_number ?? '-' }}</td>
                        <td><span class="pill pill-{{ $log->status === 'sent' ? 'green' : ($log->status === 'failed' ? 'red' : 'amber') }}">{{ $log->status }}</span></td>
                        <td class="small">{{ $log->attempts }}</td>
                        <td class="tiny muted" style="max-width:280px">{{ \Illuminate\Support\Str::limit($log->provider_response, 90) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center muted" style="padding:2rem">Belum ada log notifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel">{{ $logs->links() }}</div>
</div>
@endsection
