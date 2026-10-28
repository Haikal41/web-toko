@php
    $payment = $order->payment();
    $paymentLabels = [
        'cash' => 'Tunai',
        'qris' => 'QRIS',
        'demo' => 'Pembayaran demo',
        'bank_transfer' => 'Transfer bank',
        'ewallet' => 'E-Wallet',
    ];
    $paymentLabel = $payment?->payment_type
        ? ($paymentLabels[$payment->payment_type] ?? ucfirst(str_replace('_', ' ', $payment->payment_type)))
        : '-';
    $receiptStatus = $order->hasReceipt() ? 'LUNAS' : ($order->status === 'batal' ? 'DIBATALKAN' : 'BELUM DIBAYAR');
    $cashReceived = $order->cash_received !== null ? (float) $order->cash_received : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Struk {{ $order->order_number }} — {{ store_name() }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #111;
            margin: 0;
            padding: 16px;
            background: #f1f1f4;
            font-size: 12px;
            line-height: 1.35;
        }
        .receipt {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 6mm 5mm;
            border-radius: 6px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
        }
        .store { text-align: center; }
        .store h1 { font-size: 15px; margin: 0; letter-spacing: .3px; }
        .store p { margin: 2px 0 0; font-size: 11px; }
        hr { border: 0; border-top: 1px dashed #888; margin: 8px 0; }
        .meta div, .row { display: flex; justify-content: space-between; gap: 10px; }
        .meta div { margin: 1px 0; }
        .meta span:first-child { white-space: nowrap; }
        .meta span:last-child { text-align: right; }
        .items { width: 100%; border-collapse: collapse; }
        .items td { padding: 2px 0; vertical-align: top; }
        .items .name { padding-right: 6px; }
        .items .amount { text-align: right; white-space: nowrap; }
        .item-note { font-size: 11px; padding-left: 10px !important; }
        .totals .row { margin: 2px 0; }
        .totals .grand { font-size: 14px; font-weight: 700; margin-top: 5px; }
        .status {
            border: 1px solid #111;
            text-align: center;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 4px;
            margin: 8px 0;
        }
        .status.pending { border-style: dashed; }
        .thanks { text-align: center; margin-top: 10px; font-size: 11px; }
        .actions {
            width: 80mm;
            max-width: 100%;
            margin: 14px auto 0;
            display: flex;
            gap: 8px;
        }
        .actions button, .actions a {
            flex: 1;
            padding: 11px;
            border: 0;
            border-radius: 7px;
            background: #5b3df5;
            color: #fff;
            font: inherit;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }
        .actions a { background: #ece9f9; color: #352c63; }
        @media print {
            @page { size: 80mm auto; margin: 2mm; }
            body { background: #fff; padding: 0; font-size: 11px; }
            .receipt { width: 100%; padding: 0; border-radius: 0; box-shadow: none; }
            .actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="store">
            <h1>{{ store_name() }}</h1>
            @if(setting('store_address'))
                <p>{{ setting('store_address') }}</p>
            @endif
            @if(store_phone())
                <p>{{ store_phone() }}</p>
            @endif
        </div>

        <hr>

        <div class="meta">
            <div><span>No. pesanan</span><span>{{ $order->order_number }}</span></div>
            <div><span>Tanggal</span><span>{{ ($order->paid_at ?? $order->created_at)->format('d/m/Y H:i') }}</span></div>
            <div><span>Kasir</span><span>{{ $order->creator?->name ?? 'Online' }}</span></div>
            <div><span>Pembeli</span><span>{{ $order->customer_name }}</span></div>
            @if($order->customer_phone)
                <div><span>WhatsApp</span><span>{{ $order->customer_phone }}</span></div>
            @endif
            <div><span>Sumber</span><span>{{ $order->channelLabel() }}</span></div>
            <div><span>Pemenuhan</span><span>{{ $order->fulfillmentLabel() }}</span></div>
        </div>

        <hr>

        <table class="items">
            @foreach($order->items as $item)
                <tr>
                    <td class="name">{{ $item->quantity }}x {{ $item->product_name }}@if($item->variant_name) ({{ $item->variant_name }})@endif</td>
                    <td class="amount">{{ rupiah($item->subtotal) }}</td>
                </tr>
                @if($item->personalization_text)
                    <tr><td colspan="2" class="item-note">&#9998; {{ $item->personalization_text }}</td></tr>
                @endif
            @endforeach
        </table>

        <hr>

        <div class="totals">
            <div class="row"><span>Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
            @if((float) $order->discount > 0)
                <div class="row"><span>Diskon</span><span>-{{ rupiah($order->discount) }}</span></div>
            @endif
            <div class="row"><span>Ongkir</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
            <div class="row grand"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>
            @if($cashReceived !== null)
                <div class="row"><span>Tunai diterima</span><span>{{ rupiah($cashReceived) }}</span></div>
                <div class="row"><span>Kembalian</span><span>{{ rupiah($order->change_amount) }}</span></div>
            @endif
        </div>

        <div class="status {{ $order->hasReceipt() ? '' : 'pending' }}">{{ $receiptStatus }}</div>

        <div class="meta">
            <div><span>Pembayaran</span><span>{{ $paymentLabel }}</span></div>
            <div><span>Status</span><span>{{ $order->statusLabel() }}</span></div>
        </div>

        <p class="thanks">Terima kasih sudah berbelanja!<br>Barang custom tidak dapat ditukar.</p>
    </div>

    <div class="actions">
        <button type="button" onclick="window.print()">Cetak / simpan struk</button>
        @if(request()->routeIs('admin.orders.receipt'))
            <a href="{{ route('admin.orders.show', $order) }}">Kembali</a>
        @else
            <a href="{{ route('home') }}">Beranda</a>
        @endif
    </div>

    @if(request('auto'))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 350);
            });
        </script>
    @endif
</body>
</html>
