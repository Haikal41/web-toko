@extends('layouts.admin')

@section('title', 'Buat Pesanan')
@section('crumb', 'Transaksi / Buat Pesanan')

@section('content')
<form action="{{ route('admin.pos.store') }}" method="POST" id="pos-form">
    @csrf
    <div class="admin-grid-2">
        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <h3 style="font-size:1.02rem">Data pembeli</h3>
                <div class="form-grid mt-2">
                    <div class="field">
                        <label class="label">Nama pembeli</label>
                        <input class="input" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    <div class="field">
                        <label class="label">Nomor WhatsApp <span>(opsional)</span></label>
                        <input class="input" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="08xxxxxxxxxx">
                        <div class="help">Kosongkan untuk pembeli walk-in tanpa WhatsApp.</div>
                    </div>
                    <div class="field full">
                        <label class="checkbox-row"><input type="checkbox" name="delivery" value="1" id="delivery-toggle" @checked(old('delivery'))> Kirim ke alamat (ongkir {{ rupiah($shipping) }})</label>
                    </div>
                    <div class="field full" id="address-field" style="display:none">
                        <label class="label">Alamat pengiriman</label>
                        <textarea class="textarea" name="customer_address" placeholder="Jalan, nomor rumah, kota">{{ old('customer_address') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="panel-head">
                    <div>
                        <h3>Item pesanan</h3>
                        <p>Pilih produk, varian, dan jumlah</p>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="add-row">+ Tambah item</button>
                </div>
                <div class="panel-body">
                    <div id="pos-items" style="display:flex;flex-direction:column;gap:.9rem"></div>
                    @error('items')<div class="alert alert-error small mt-2">{{ $message }}</div>@enderror
                    @error('items.*')<div class="alert alert-error small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Catatan</h3>
                <textarea class="textarea mt-2" name="notes" placeholder="Mis. ambil di toko sore ini">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="card panel" style="align-self:start;position:sticky;top:95px">
            <h3 style="font-size:1.02rem">Pembayaran</h3>
            <div class="divider"></div>

            <div class="summary-row"><span class="muted">Subtotal</span><span id="pos-subtotal">Rp0</span></div>
            <div class="summary-row"><span class="muted">Ongkir</span><span id="pos-shipping">Rp0</span></div>

            <div class="field mt-2">
                <label class="label">Kupon <span>(opsional)</span></label>
                <input class="input" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="HADIAH10">
            </div>

            <div class="summary-row total"><span>Total</span><span id="pos-total">Rp0</span></div>
            <p class="help">Total final dihitung ulang oleh sistem (termasuk kupon).</p>

            <div class="field">
                <label class="label">Metode bayar</label>
                <label class="checkbox-row"><input type="radio" name="payment_method" value="cash" @checked(old('payment_method', 'cash') === 'cash')> Tunai (langsung lunas)</label>
                <label class="checkbox-row"><input type="radio" name="payment_method" value="qris" @checked(old('payment_method') === 'qris')> QRIS / online (kirim link bayar)</label>
            </div>

            <div class="field" id="cash-field">
                <label class="label">Uang tunai diterima</label>
                <input type="number" class="input" name="cash_received" id="cash-received" value="{{ old('cash_received') }}" min="0" step="any" inputmode="numeric" placeholder="0">
                <div class="help" id="change-preview">Kembalian (perkiraan): Rp0</div>
                @error('cash_received')<div class="help" style="color:var(--red)">{{ $message }}</div>@enderror
                @error('items')<div class="help" style="color:var(--red)">{{ $message }}</div>@enderror
            </div>

            <button class="btn btn-lg btn-block mt-2" type="submit">Simpan &amp; cetak struk</button>
            <p class="help">Pesanan tunai langsung ditandai lunas dan struk 80mm dibuka otomatis.</p>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost btn-block mt-1">Batal</a>
        </div>
    </div>
</form>

<template id="pos-row-template">
    <div class="pos-row card" style="padding:1rem;background:#faf9fe">
        <div class="form-grid" style="align-items:end">
            <div class="field" style="grid-column:span 2">
                <label class="label">Produk</label>
                <select class="select" name="items[__INDEX__][product_id]" data-product required></select>
            </div>
            <div class="field">
                <label class="label">Varian</label>
                <select class="select" name="items[__INDEX__][variant_id]" data-variant></select>
            </div>
            <div class="field">
                <label class="label">Qty</label>
                <input type="number" class="input" name="items[__INDEX__][quantity]" value="1" min="1" max="999" data-qty required>
            </div>
            <div class="field" style="grid-column:span 2">
                <label class="label">Personalisasi <span>(jika wajib)</span></label>
                <input class="input" name="items[__INDEX__][personalization_text]" maxlength="60" data-persona>
            </div>
            <div class="field">
                <label class="label">Catatan personalisasi</label>
                <input class="input" name="items[__INDEX__][personalization_notes]" maxlength="200">
            </div>
            <div class="field" style="justify-content:end">
                <label class="label">Subtotal</label>
                <div class="bold" data-line-total>Rp0</div>
            </div>
        </div>
        <button type="button" class="btn btn-ghost btn-sm" data-remove style="color:var(--red);margin-top:.4rem">Hapus item</button>
    </div>
</template>
@endsection

@push('scripts')
<script>
    const POS_PRODUCTS = @json($posProducts);
    const SHIPPING = {{ (float) $shipping }};
    const rupiah = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(value));

    let nextIndex = 0;
    let currentTotal = 0;

    function addRow() {
        const template = document.getElementById('pos-row-template').innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = template.trim();
        const row = wrapper.firstElementChild;

        const productSelect = row.querySelector('[data-product]');
        productSelect.innerHTML = '<option value="">— Pilih produk —</option>' + POS_PRODUCTS.map((product) =>
            `<option value="${product.id}">${product.name} — ${rupiah(product.price)} (stok ${product.stock})</option>`
        ).join('');

        productSelect.addEventListener('change', () => {
            const product = POS_PRODUCTS.find((item) => String(item.id) === productSelect.value);
            const variantSelect = row.querySelector('[data-variant]');

            if (!product) {
                variantSelect.innerHTML = '<option value="">-</option>';
            } else if (product.variants.length === 0) {
                variantSelect.innerHTML = '<option value="">-</option>';
            } else {
                variantSelect.innerHTML = '<option value="">Tanpa varian</option>' + product.variants.map((variant) =>
                    `<option value="${variant.id}">${variant.name}${variant.adjustment > 0 ? ' (+' + rupiah(variant.adjustment) + ')' : ''}</option>`
                ).join('');
            }

            updateRow(row);
        });

        row.querySelector('[data-variant]').addEventListener('change', () => updateRow(row));
        row.querySelector('[data-qty]').addEventListener('input', () => updateRow(row));
        row.querySelector('[data-remove]').addEventListener('click', () => {
            row.remove();
            updateSummary();
        });

        document.getElementById('pos-items').appendChild(row);
        updateSummary();
    }

    function updateRow(row) {
        const product = POS_PRODUCTS.find((item) => String(item.id) === row.querySelector('[data-product]').value);
        const variantId = row.querySelector('[data-variant]').value;
        const variant = product?.variants.find((item) => String(item.id) === variantId);
        const qty = Math.max(1, parseInt(row.querySelector('[data-qty]').value || '1', 10));
        const price = (product?.price || 0) + (variant?.adjustment || 0);

        row.querySelector('[data-line-total]').textContent = rupiah(price * qty);
        updateSummary();
    }

    function updateSummary() {
        let subtotal = 0;

        document.querySelectorAll('#pos-items .pos-row').forEach((row) => {
            const product = POS_PRODUCTS.find((item) => String(item.id) === row.querySelector('[data-product]').value);
            const variantId = row.querySelector('[data-variant]').value;
            const variant = product?.variants.find((item) => String(item.id) === variantId);
            const qty = Math.max(1, parseInt(row.querySelector('[data-qty]').value || '1', 10));
            subtotal += ((product?.price || 0) + (variant?.adjustment || 0)) * qty;
        });

        const delivery = document.getElementById('delivery-toggle').checked;
        const shipping = delivery ? SHIPPING : 0;

        currentTotal = subtotal + shipping;

        document.getElementById('pos-subtotal').textContent = rupiah(subtotal);
        document.getElementById('pos-shipping').textContent = rupiah(shipping);
        document.getElementById('pos-total').textContent = rupiah(currentTotal);
        updateCashPreview();
    }

    function updateCashPreview() {
        const input = document.getElementById('cash-received');
        const preview = document.getElementById('change-preview');
        const received = parseFloat(input.value || '0');

        preview.style.color = '';

        if (!received) {
            preview.textContent = 'Kembalian (perkiraan): Rp0';
            return;
        }

        const change = received - currentTotal;

        if (change < 0) {
            preview.textContent = 'Kurang ' + rupiah(Math.abs(change)) + ' dari total';
            preview.style.color = 'var(--red)';
            return;
        }

        preview.textContent = 'Kembalian (perkiraan): ' + rupiah(change);
        preview.style.color = 'var(--green)';
    }

    function toggleAddress() {
        const delivery = document.getElementById('delivery-toggle').checked;
        document.getElementById('address-field').style.display = delivery ? '' : 'none';
        updateSummary();
    }

    function togglePaymentMethod() {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value;
        const isCash = method !== 'qris';
        const cashField = document.getElementById('cash-field');
        const cashInput = document.getElementById('cash-received');

        cashField.style.display = isCash ? '' : 'none';
        cashInput.required = isCash;
        updateCashPreview();
    }

    document.getElementById('add-row').addEventListener('click', addRow);
    document.getElementById('delivery-toggle').addEventListener('change', toggleAddress);
    document.getElementById('cash-received').addEventListener('input', updateCashPreview);
    document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
        input.addEventListener('change', togglePaymentMethod);
    });
    toggleAddress();
    togglePaymentMethod();
    addRow();
</script>
@endpush
