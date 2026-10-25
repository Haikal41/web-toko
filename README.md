# Multiverse Aksesoris

Toko online aksesoris custom (gantungan kunci, boneka, pigura, piala) dengan fitural personalisasi nama, pembayaran Midtrans, notifikasi WhatsApp otomatis, dashboard admin, dan absensi karyawan berbasis QR.

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

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@multiverse.test` | `password` |
| Karyawan | `karyawan@multiverse.test` | `password` |
| Pembeli | `pembeli@multiverse.test` | `password` |

`php artisan db:seed` mengisi katalog, pengaturan toko, kupon, pesanan contoh, dan riwayat absensi.

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

## Alur Pembayaran

1. Pembeli menyelesaikan checkout → pesanan berstatus `pending`.
2. Midtrans mengirim webhook ke `POST /webhooks/midtrans` → pesanan menjadi `paid`, stok berkurang, notifikasi WA dikirim.
3. Admin mengubah status: `paid` → `diproses` → `dikirim` → `selesai`.
4. Pembatalan pada pesanan yang sudah lunas mengembalikan stok.

## Absensi Karyawan

Admin membuat QR token di `Admin → Absensi` (berlaku 60 detik). Karyawan memindai token tersebut di `Absensi` untuk mencatat jam masuk, lalu memindai sekali lagi untuk jam pulang. Status `telat` dihitung otomatis dari pengaturan jam kerja.

## Pengujian

```bash
php artisan test
```

Test suite memakai SQLite in-memory (`phpunit.xml`) dan mencakup katalog, autentikasi, keranjang, checkout, webhook Midtrans, panel admin, absensi, akun, serta kupon.
