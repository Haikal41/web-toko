@php($totalItems = $order->items->sum('quantity'))
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Pengiriman {{ $order->order_number }} — {{ store_name() }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: #111;
            margin: 0;
            padding: 18px;
            background: #eef0f4;
            font-size: 13px;
        }
        .label {
            width: 100mm;
            height: 150mm;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #d7dae2;
            border-radius: 6px;
            padding: 6mm 6mm 5mm;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 32px rgba(15, 23, 42, .1);
        }
        .label-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 8px;
            border-bottom: 2px solid #111;
            padding-bottom: 7px;
        }
        .label-head .store { font-size: 14px; font-weight: 800; line-height: 1.2; }
        .label-head .store small { display: block; font-size: 9px; font-weight: 600; letter-spacing: 1.2px; color: #555; margin-top: 3px; }
        .label-head .tag {
            border: 1.5px solid #111;
            border-radius: 4px;
            padding: 3px 7px;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .8px;
            white-space: nowrap;
        }
        .order-no {
            margin: 9px 0 8px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: .4px;
            word-break: break-word;
        }
        .section-title {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.4px;
            color: #555;
            margin-bottom: 3px;
        }
        .recipient {
            border: 1.5px solid #111;
            border-radius: 5px;
            padding: 8px 10px;
        }
        .recipient .name { font-size: 16px; font-weight: 800; line-height: 1.25; }
        .recipient .phone { font-size: 13px; font-weight: 600; margin-top: 2px; }
        .recipient .address { font-size: 12.5px; line-height: 1.4; margin-top: 6px; overflow-wrap: anywhere; }
        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }
        .detail-box {
            border: 1px solid #c9cdd6;
            border-radius: 5px;
            padding: 7px 9px;
        }
        .detail-box .value { font-size: 13px; font-weight: 700; }
        .notes {
            margin-top: 10px;
            border: 1px dashed #9aa0ad;
            border-radius: 5px;
            padding: 7px 9px;
            font-size: 11.5px;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .notes strong { display: block; font-size: 9px; letter-spacing: 1.2px; color: #555; margin-bottom: 2px; }
        .label-foot {
            margin-top: auto;
            border-top: 1px solid #c9cdd6;
            padding-top: 6px;
            font-size: 9px;
            line-height: 1.5;
            color: #555;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .actions {
            width: 100mm;
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
        .actions a { background: #e8e6f4; color: #352c63; }
        @media print {
            @page { size: 100mm 150mm; margin: 0; }
            body { background: #fff; padding: 0; }
            .label {
                width: 100mm;
                height: 150mm;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }
            .actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="label-head">
            <div class="store">
                {{ store_name() }}
                <small>LABEL PENGIRIMAN INTERNAL</small>
            </div>
            <span class="tag">KIRIM</span>
        </div>

        <div class="order-no">{{ $order->order_number }}</div>

        <div class="section-title">PENERIMA</div>
        <div class="recipient">
            <div class="name">{{ $order->customer_name }}</div>
            @if($order->customer_phone)
                <div class="phone">{{ $order->customer_phone }}</div>
            @endif
            <div class="address">{{ $order->customer_address }}</div>
        </div>

        <div class="details">
            <div class="detail-box">
                <div class="section-title">JUMLAH ITEM</div>
                <div class="value">{{ $totalItems }} item</div>
            </div>
            <div class="detail-box">
                <div class="section-title">TANGGAL</div>
                <div class="value">{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        @if($order->notes)
            <div class="notes">
                <strong>CATATAN</strong>
                {{ $order->notes }}
            </div>
        @endif

        <div class="label-foot">
            <span>
                Pengirim: {{ store_name() }}<br>
                {{ store_phone() }}
            </span>
            <span style="text-align:right">
                Bukan resi kurir resmi.<br>
                Label internal toko.
            </span>
        </div>
    </div>

    <div class="actions">
        <button type="button" onclick="window.print()">Cetak label</button>
        <a href="{{ route('admin.orders.show', $order) }}">Kembali</a>
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
