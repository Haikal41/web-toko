@extends('layouts.app')

@section('title', 'Pembayaran '.$order->order_number.' — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="steps">
            <span class="step"><span class="num">1</span> Keranjang</span>
            <span class="sep"></span>
            <span class="step"><span class="num">2</span> Alamat</span>
            <span class="sep"></span>
            <span class="step active"><span class="num">3</span> Pembayaran</span>
        </div>
        <h1>Pembayaran</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="card panel pay-card">
        <div class="text-center">
            <span class="pill pill-amber">Menunggu pembayaran</span>
            <h3 style="margin-top:.8rem">Pesanan {{ $order->order_number }}</h3>
            <p class="small muted mb-0">Selesaikan pembayaran sebelum pesanan diproses.</p>
        </div>

        <div class="pay-total">
            <span>Total tagihan</span>
            <strong>{{ rupiah($order->total) }}</strong>
        </div>

        <div class="divider"></div>
        <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
        @if((float) $order->discount > 0)
            <div class="summary-row"><span class="muted">Diskon</span><span>-{{ rupiah($order->discount) }}</span></div>
        @endif
        <div class="summary-row"><span class="muted">Ongkos kirim</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
        <div class="summary-row total"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>

        <div class="divider"></div>

        @if($snapToken)
            <button id="pay-button" class="btn btn-lg btn-block">Bayar Sekarang</button>
            <p class="help text-center mt-1">Pembayaran diproses aman oleh Midtrans (QRIS, Virtual Account, e-wallet).</p>

            <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
            @push('scripts')
                <script>
                    document.getElementById('pay-button')?.addEventListener('click', function () {
                        window.snap.pay(@json($snapToken), {
                            onSuccess: function () { window.location.href = @json($finishUrl); },
                            onPending: function () { window.location.href = @json($finishUrl); },
                            onError: function () { alert('Pembayaran gagal. Silakan coba lagi.'); },
                            onClose: function () { alert('Kamu menutup jendela pembayaran sebelum menyelesaikan transaksi.'); }
                        });
                    });
                </script>
            @endpush
        @else
            <div class="alert alert-info">
                <strong>Mode demo.</strong> Kunci Midtrans belum diisi, jadi pembayaran bisa disimulasikan untuk mencoba alur pesanan dan notifikasi WhatsApp.
            </div>

            <form action="{{ $demoUrl }}" method="POST">
                @csrf
                <button class="btn btn-lg btn-block" type="submit">Simulasikan Pembayaran Berhasil</button>
            </form>

            <p class="help text-center mt-2">
                Untuk pembayaran nyata, isi <code>MIDTRANS_SERVER_KEY</code> dan <code>MIDTRANS_CLIENT_KEY</code> di file <code>.env</code>.
            </p>
        @endif

        @auth
            <a href="{{ route('account.index') }}" class="btn btn-ghost btn-block mt-2">Bayar nanti &mdash; lihat pesanan di akun</a>
        @else
            <a href="{{ route('track.index') }}" class="btn btn-ghost btn-block mt-2">Bayar nanti &mdash; simpan nomor pesanan untuk dilacak</a>
        @endauth
    </div>
</div>
@endsection
