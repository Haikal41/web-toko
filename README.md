# Multiverse Aksesoris

Toko online aksesoris custom (gantungan kunci, boneka, pigura, piala) dengan fitur personalisasi nama, pembayaran Midtrans, notifikasi WhatsApp otomatis, dan dashboard operasional untuk owner serta kasir. Pembeli tidak perlu membuat akun: checkout sebagai tamu dan lacak pesanan dengan nomor pesanan + nomor WhatsApp.

Spesifikasi lengkap ada di [PRD.md](PRD.md).

## Stack

- Laravel 11, PHP 8.3, MySQL 8
- Tailwind CSS 4 + Vite
- Midtrans Snap (QRIS, Virtual Account, e-wallet)
- Fonnte (WhatsApp API)
- Chart.js (grafik dashboard)

## Menjalankan Proyek

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# buat database sesuai DB_* pada .env, lalu:
php artisan migrate --seed
php artisan storage:link

npm run build        # atau `npm run dev` saat pengembangan
php artisan serve
```

Queue worker perlu berjalan agar notifikasi WhatsApp terkirim:

```bash
php artisan queue:work
```

## Akun Demo

| Peran | Email | Password | Akses |
|---|---|---|---|
| Owner | `owner@multiverse.test` | `password` | Semua menu, laporan, pengaturan, kelola kasir |
| Kasir | `kasir@multiverse.test` | `password` | Pesanan, POS, pembatalan, lihat stok produk |
| Kasir 2 | `kasir2@multiverse.test` | `password` | Sama seperti kasir |

`php artisan db:seed` mengisi katalog, pengaturan toko, kupon, akun owner/kasir, dan pesanan contoh (online + POS).

## Peran & Hak Akses

- **Owner** — dashboard omzet & grafik, pesanan, POS, laporan/export, katalog (CRUD produk, kategori, varian, foto), kupon, kelola kasir, pengaturan toko, dan pengaturan WhatsApp.
- **Kasir** — dashboard ringkas, daftar/detail pesanan, buat pesanan manual (POS), cetak struk 80mm, cetak label kirim 100×150mm, kirim ulang struk via WhatsApp, ubah status, dan batalkan pesanan sampai status `diproses`.
- **Pembeli** — tanpa akun. Checkout sebagai tamu, lalu lacak pesanan di `/lacak` dengan nomor pesanan dan nomor WhatsApp. Struk pembayaran dapat dicetak/disimpan lewat tautan bertanda tangan tanpa login. Akun `customer` lama tetap bisa login dan melihat riwayat pesanannya.

## Konfigurasi

Isi `.env` untuk mengaktifkan integrasi eksternal:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

WA_PROVIDER=fonnte
WA_API_KEY=
WA_ADMIN_NUMBER=6289502646082
WA_SENDER=

STORE_PHONE=6289502646082
STORE_EMAIL=alifhaikl41@gmail.com

MAIL_FROM_ADDRESS="alifhaikl41@gmail.com"
```

Tanpa `WA_API_KEY`, pengiriman WhatsApp hanya dicatat ke log (mode simulasi). Tanpa `MIDTRANS_SERVER_KEY`, halaman pembayaran menampilkan tombol pembayaran demo yang menandai pesanan lunas secara manual.

Tautan struk di WhatsApp memakai `APP_URL`. Saat produksi, isi `APP_URL` dengan domain publik (HTTPS) agar tautan dapat dibuka di ponsel pembeli; `http://localhost` hanya cocok untuk pengembangan.

## Alur Pembelian

### Checkout online (tamu)

1. Pembeli menambah produk ke keranjang dan membuka checkout tanpa login.
2. Pembeli mengisi nama, nomor WhatsApp, dan alamat; pesanan dibuat berstatus `pending`.
3. Pembeli diarahkan ke tautan pembayaran bertanda tangan (berlaku 7 hari).
4. Midtrans mengirim webhook ke `POST /webhooks/midtrans` → pesanan menjadi `paid`, stok berkurang, notifikasi WA berisi ringkasan + tautan struk dikirim.
5. Pembeli dapat melacak pesanan kapan saja di menu **Lacak Pesanan**, termasuk membuka struk bila sudah lunas.

### Pesanan manual / POS (kasir)

1. Kasir membuka **Buat Pesanan** di panel admin.
2. Kasir memilih produk/varian, mengisi personalisasi bila wajib, memilih ambil di toko atau kirim, lalu memilih metode bayar.
3. **Tunai** — kasir mengisi uang yang diterima; sistem menghitung kembalian, menandai pesanan `paid`, mengurangi stok, lalu membuka struk thermal 80mm untuk dicetak otomatis.
4. **QRIS/online** — pesanan tetap `pending`; kasir mengirim tautan pembayaran ke pembeli via WhatsApp.
5. Pesanan yang dikirim memiliki tombol **Cetak resi** untuk label pengiriman internal 100×150mm.

### Struk & label pengiriman

- Struk pembayaran tersedia untuk pesanan online maupun POS setelah lunas. Ukuran cetak thermal 80mm, menampilkan rincian item, total, metode bayar, serta uang diterima dan kembalian untuk transaksi tunai.
- Struk dapat dibuka dari halaman selesai checkout, halaman lacak, akun pembeli lama, detail pesanan admin, dan tautan WhatsApp. Tautan struk publik memakai signed URL berlaku 30 hari sehingga pembeli tamu tidak perlu akun.
- Label kirim 100×150mm hanya tersedia untuk pesanan berstatus kirim (bukan ambil di toko). Label memuat penerima, alamat, nomor pesanan, jumlah item, dan catatan; label ini internal toko, bukan resi resmi kurir.
- Kasir dapat mengirim ulang struk lewat tombol **Kirim ulang struk**. Log WhatsApp membedakan `payment_receipt` (otomatis) dan `manual_resend`.

### Status & pembatalan

- Alur status: `pending` → `paid` → `diproses` → `dikirim` → `selesai`, atau `batal`.
- Kasir dapat membatalkan pesanan berstatus `pending`, `paid`, atau `diproses`.
- Owner dapat membatalkan pesanan pada status apa pun.
- Pembatalan pesanan yang sudah lunas mengembalikan stok, mencatat alasan, pelaku, dan waktu.
- Setiap perubahan status tercatat di `order_status_logs` (siapa, dari/ke status, catatan, waktu).

## Pengujian

```bash
php artisan test
```

Test suite memakai SQLite in-memory (`phpunit.xml`) dan mencakup katalog, autentikasi owner/kasir, keranjang, checkout tamu, pelacakan pesanan, POS + tunai/kembalian, struk publik bertanda tangan, label kirim, notifikasi WhatsApp (otomatis/kirim ulang), pembatalan + pengembalian stok, webhook Midtrans, panel admin, akun lama, serta kupon.
