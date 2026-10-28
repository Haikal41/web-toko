# Product Requirements Document (PRD)

## Multiverse Aksesoris — Toko Online Aksesoris

| Item | Keterangan |
|---|---|
| Nama Produk | Multiverse Aksesoris |
| Jenis Produk | Toko online (e-commerce) B2C |
| Versi Dokumen | 1.1 |
| Tanggal | 28 Oktober 2026 |
| Status | Final untuk MVP |
| Stack | Laravel 11 + MySQL 8, Midtrans, Fonnte (WhatsApp), Chart.js |
| Pemilik Dokumen | Pemilik Toko / Product Owner |

> **Catatan revisi 1.1 (28 Oktober 2026):** Modul absensi karyawan dihapus. Peran diubah menjadi `owner` dan `kasir`; pembeli tidak lagi membuat akun (checkout tamu + pelacakan pesanan). Ditambahkan pesanan manual/POS, pembatalan oleh kasir, dan audit trail perubahan status.
>
> **Catatan revisi 1.2 (28 Oktober 2026):** Struk pembayaran thermal 80mm untuk pesanan online dan POS (termasuk uang diterima/kembalian), tautan struk publik bertanda tangan 30 hari, label kirim internal 100×150mm, serta pengiriman ulang struk via WhatsApp dengan pemisahan jenis log.

---

## 1. Ringkasan & Tujuan Produk

### 1.1 Latar Belakang
Multiverse Aksesoris menjual produk aksesoris seperti gantungan kunci, boneka/plush, pigura, piala, dan produk sejenis. Saat ini penjualan masih dikelola secara manual sehingga pencatatan pesanan, konfirmasi pembayaran, pengelolaan stok, dan notifikasi ke pembeli memakan waktu serta rawan kesalahan.

### 1.2 Tujuan
Membangun website toko online yang memungkinkan pembeli melihat katalog, memesan produk (termasuk personalisasi), membayar secara online, dan menerima konfirmasi otomatis melalui WhatsApp; sekaligus memberi owner dan kasir dashboard untuk mengelola produk, pesanan (online maupun POS), laporan penjualan, dan stok.

### 1.3 Metrik Keberhasilan (3 bulan pertama)
| Metrik | Target |
|---|---|
| Conversion rate (kunjungan → pesanan lunas) | ≥ 2% |
| Pesanan lunas per bulan | ≥ 100 |
| Waktu konfirmasi WA setelah pembayaran sukses | < 1 menit |
| Waktu admin memproses pesanan baru | < 5 menit |
| Kesalahan pencatatan stok | 0 (stok berkurang otomatis) |
| Pesanan manual/POS tercatat di sistem | ≥ 90% transaksi toko |

### 1.4 Prinsip Produk
- Mobile-first: mayoritas pembeli mengakses dari ponsel.
- Sederhana: alur checkout maksimal 3 langkah.
- Transparan: status pesanan dan pembayaran jelas bagi pembeli.
- Personalisasi sebagai nilai jual utama (ukiran nama pada piala/pigura).

---

## 2. Ruang Lingkup

### 2.1 In-Scope (MVP)
1. Katalog produk dengan kategori, pencarian, filter, dan indikator stok.
2. Detail produk dengan galeri foto, varian (warna/ukuran), dan form personalisasi.
3. Checkout sebagai tamu (tanpa registrasi) serta pelacakan pesanan via nomor pesanan + WhatsApp.
4. Keranjang belanja dan checkout.
5. Pembayaran online melalui Midtrans (QRIS, Virtual Account, e-wallet).
6. Notifikasi WhatsApp otomatis via Fonnte setelah pembayaran sukses (pembeli dan owner).
7. Dashboard operasional: CRUD produk & foto, kelola pesanan, kelola stok, laporan penjualan.
8. Grafik penjualan: tren penjualan, produk terlaris, volume pesanan (khusus owner).
9. Pesanan manual/POS untuk pembeli walk-in dan pembatalan pesanan dengan pengembalian stok.
10. Role akses: `owner` (penuh) dan `kasir` (operasional pesanan/POS).

### 2.2 Out-of-Scope (MVP)
- Marketplace multi-vendor.
- Integrasi kurir otomatis (RajaOngkir/API logistik); ongkir menggunakan tarif flat.
- Aplikasi mobile native.
- Program loyalitas/poin, wishlist, dan ulasan produk (masuk roadmap fase 2).
- Multi-bahasa dan multi-mata uang.
- Integrasi mesin fingerprint/face recognition.
- Notifikasi WhatsApp untuk setiap perubahan status (MVP hanya saat pembayaran sukses).

### 2.3 Asumsi
- Satu toko tunggal, satu gudang.
- Pembayaran hanya melalui Midtrans; tidak ada COD di MVP.
- Ongkir flat per pesanan (nominal diatur admin).
- Stok dikelola manual oleh admin; tidak ada multi-lokasi.
- Nomor WhatsApp pembeli wajib diisi saat checkout online; pesanan walk-in dari kasir boleh tanpa nomor WhatsApp.
- Nama toko ditulis **Multiverse Aksesoris**.

---

## 3. Persona & User Journey

### 3.1 Persona

**P1 — Pembeli Hadiah (B2C)**
- Usia 18–35, membeli piala/pigura/gantungan kunci untuk hadiah ulang tahun, wisuda, atau kenang-kenangan.
- Butuh personalisasi nama/teks dan ingin cepat tahu pesanan diproses.
- Sering mengakses dari ponsel.

**P2 — Owner**
- Mengelola produk, stok, pesanan, laporan, pengaturan, dan akun kasir.
- Ingin melihat performa penjualan sekilas melalui grafik.

**P3 — Kasir**
- Staf toko yang melayani pembeli walk-in dan memproses pesanan masuk.
- Membutuhkan akses pesanan, POS, pembatalan terbatas, dan pencetakan struk.

### 3.2 User Journey — Pembeli
```
Buka website → Jelajahi katalog → Buka detail produk
→ Pilih varian + isi personalisasi → Tambah ke keranjang
→ Checkout sebagai tamu (isi alamat, no. WA)
→ Bayar via Midtrans → Pembayaran sukses
→ Terima notifikasi WhatsApp → Pesanan diproses kasir
→ Pesanan dikirim → Pesanan selesai
→ Lacak pesanan kapan saja dengan nomor pesanan + nomor WA
```

### 3.3 User Journey — Owner
```
Login → Dashboard (grafik penjualan & ringkasan)
→ Kelola produk/stok → Proses pesanan masuk
→ Update status pesanan → Lihat laporan & pengaturan
```

### 3.4 User Journey — Kasir
```
Login → Dashboard ringkas → Daftar pesanan
→ Buat pesanan manual/POS → Tandai tunai lunas atau kirim link QRIS
→ Ubah status / batalkan pesanan (sampai diproses) → Cetak struk & label kirim
```

---

## 4. Kebutuhan Fungsional

### 4.1 Katalog Produk
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-01 | Pengunjung dapat melihat daftar produk dengan foto, nama, harga, dan status stok. | Must |
| F-02 | Kategori default: Gantungan Kunci, Boneka, Pigura, Piala, Lainnya. Admin dapat menambah/mengubah kategori. | Must |
| F-03 | Pencarian produk berdasarkan nama dan deskripsi. | Must |
| F-04 | Filter berdasarkan kategori dan rentang harga; urutkan berdasarkan terbaru, harga, dan terlaris. | Should |
| F-05 | Pagination daftar produk. | Must |
| F-06 | Produk dengan stok 0 ditandai "Habis" dan tidak bisa dibeli. | Must |
| F-07 | Halaman detail produk: galeri foto, deskripsi, varian, harga, stok, dan tombol tambah ke keranjang. | Must |

### 4.2 Personalisasi Produk
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-08 | Produk dapat ditandai "memerlukan personalisasi" oleh admin. | Must |
| F-09 | Pembeli mengisi teks personalisasi (maks. 60 karakter) dan catatan tambahan (maks. 200 karakter) di halaman detail. | Must |
| F-10 | Personalisasi wajib untuk kategori Piala dan Pigura; form tidak boleh kosong. | Must |
| F-11 | Teks personalisasi tersimpan per item pesanan dan tampil di detail pesanan admin, halaman pembeli, dan template WhatsApp. | Must |
| F-12 | Preview teks personalisasi ditampilkan sebelum ditambahkan ke keranjang. | Should |

### 4.3 Akun & Akses Pembeli
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-13 | Checkout sebagai tamu tanpa registrasi: cukup nama, nomor WhatsApp, dan alamat. | Must |
| F-14 | Login/logout untuk owner dan kasir; akun `customer` lama tetap dapat login. | Must |
| F-15 | Pelacakan pesanan publik via nomor pesanan + nomor WhatsApp (tanpa akun). | Must |
| F-16 | Riwayat pesanan untuk akun lama beserta status dan detail pembayaran. | Should |
| F-17 | Owner dapat mengubah profil; kasir dikelola owner. | Should |

### 4.4 Keranjang & Checkout
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-18 | Tambah, ubah jumlah, dan hapus item dari keranjang. | Must |
| F-19 | Keranjang menyimpan varian dan teks personalisasi per item. | Must |
| F-20 | Checkout tamu: isi nama penerima, nomor WhatsApp, alamat, dan ringkasan total. | Must |
| F-21 | Validasi stok ulang saat checkout; tolak jika stok tidak cukup. | Must |
| F-22 | Kode kupon diskon (persentase/nominal) dikelola owner. | Should |
| F-23 | Setelah checkout, pesanan dibuat dengan status `pending` dan pembeli diarahkan ke tautan pembayaran Midtrans bertanda tangan (berlaku 7 hari). | Must |

### 4.5 Pembayaran (Midtrans)
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-24 | Integrasi Midtrans Snap untuk QRIS, Virtual Account, dan e-wallet. | Must |
| F-25 | Halaman status pembayaran: menunggu, sukses, gagal/kadaluarsa. | Must |
| F-26 | Webhook Midtrans memverifikasi signature key sebelum memproses status. | Must |
| F-27 | Status `settlement`/`capture` mengubah pesanan menjadi `paid` dan mengurangi stok. | Must |
| F-28 | Status gagal/kadaluarsa mengembalikan stok jika sebelumnya sudah dialokasikan. | Must |
| F-29 | Payment record menyimpan `order_id`, `transaction_id`, `payment_type`, `gross_amount`, dan `status`. | Must |

### 4.6 Notifikasi WhatsApp (Fonnte)
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-30 | Notifikasi dikirim otomatis setelah pembayaran sukses (event `OrderPaid`). | Must |
| F-31 | Penerima: pembeli (nomor dari checkout) dan admin (nomor dari konfigurasi). | Must |
| F-32 | Template pembeli: nomor pesanan, daftar item + personalisasi, total, metode bayar, uang diterima/kembalian (tunai), status pemenuhan, dan tautan struk publik. | Must |
| F-33 | Template admin: nomor pesanan, nama pembeli, total, jumlah item, metode bayar, pemenuhan, dan tautan pesanan. | Must |
| F-34 | Pengiriman melalui queue job agar tidak memperlambat webhook; retry 3x dengan backoff. | Must |
| F-35 | Setiap pengiriman dicatat di `whatsapp_logs` (penerima, jenis pesan, status, response provider, waktu). | Must |
| F-36 | Fallback email jika pengiriman WA gagal setelah retry. | Should |
| F-37 | Tombol "Tes Kirim WA" di dashboard owner. | Should |
| F-69 | Kirim ulang struk manual dari detail pesanan; log dibedakan `payment_receipt` vs `manual_resend`. | Must |

### 4.7 Dashboard Operasional
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-38 | CRUD produk: nama, kategori, deskripsi, harga, stok, status personalisasi, varian (khusus owner). | Must |
| F-39 | Upload beberapa foto produk; foto utama dapat dipilih; hapus foto (khusus owner). | Must |
| F-40 | Daftar pesanan dengan filter status, sumber (online/POS), tanggal, dan pencarian nomor pesanan. | Must |
| F-41 | Detail pesanan: data pembeli, alamat, item, personalisasi, pembayaran, riwayat status, tombol ubah status. | Must |
| F-42 | Kelola stok dan penanda stok menipis; kasir hanya melihat stok. | Must |
| F-43 | Laporan penjualan dengan filter periode dan export CSV (khusus owner). | Must |
| F-44 | Ringkasan dashboard: omzet hari ini/bulan ini, pesanan pending, stok menipis. | Must |
| F-45 | Kelola ongkir flat dan kode kupon (khusus owner). | Should |
| F-46 | Kelola akun kasir: tambah, ubah, nonaktifkan, hapus (khusus owner). | Must |
| F-47 | Pengaturan toko: nama, logo, nomor admin WA (khusus owner). | Should |
| F-63 | Kasir dapat membuat pesanan manual/POS: pilih produk/varian, personalisasi, kirim/ambil di toko, kupon, dan metode bayar. | Must |
| F-64 | Pembayaran tunai POS langsung `paid`; QRIS menyisakan `pending` + tautan pembayaran. | Must |
| F-65 | Kasir dapat membatalkan pesanan sampai status `diproses`; owner dapat membatalkan status apa pun. | Must |
| F-66 | Pembatalan lunas mengembalikan stok dan mencatat pelaku, alasan, serta waktu. | Must |
| F-67 | Setiap perubahan status dicatat di `order_status_logs` (dari/ke status, pelaku, catatan, waktu). | Must |
| F-68 | Struk pembayaran thermal 80mm untuk pesanan online dan POS; transaksi tunai menampilkan uang diterima dan kembalian; struk POS tunai terbuka otomatis. | Must |
| F-70 | Struk publik dapat dibuka tanpa akun melalui signed URL berlaku 30 hari, dari halaman selesai, lacak, akun, detail admin, dan WhatsApp. | Must |
| F-71 | Label kirim internal 100×150mm (penerima, alamat, nomor pesanan, jumlah item, catatan) hanya untuk pesanan yang dikirim; bukan resi resmi kurir. | Should |

### 4.8 Grafik Penjualan
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-48 | Grafik tren penjualan (garis) dengan filter harian/bulanan/tahunan. | Must |
| F-49 | Grafik produk terlaris (bar horizontal, top 10 berdasarkan unit dan omzet). | Must |
| F-50 | Grafik volume pesanan per status dan per periode. | Must |
| F-51 | Data grafik hanya dari pesanan berstatus `paid` ke atas (tidak termasuk `pending`/`batal`). | Must |
| F-52 | Grafik responsif dan menampilkan tooltip nilai Rupiah. | Should |

### 4.9 Peran & Hak Akses
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-53 | Role `owner`: akses penuh ke dashboard omzet/grafik, pesanan, POS, laporan, katalog, kategori, kupon, kasir, dan pengaturan. | Must |
| F-54 | Role `kasir`: dashboard ringkas, daftar/detail pesanan, POS, ubah status, batal sampai `diproses`, cetak struk, dan lihat produk/stok. | Must |
| F-55 | Role `customer` dipertahankan untuk akun lama agar tetap bisa login dan melihat riwayat pesanan. | Should |
| F-56 | Registrasi publik dihapus; pembeli baru menggunakan checkout tamu. | Must |
| F-57 | Middleware role melindungi seluruh route admin; kasir mendapat 403 pada menu owner-only. | Must |
| F-58 | Menu owner-only disembunyikan dari sidebar kasir. | Must |

---

## 5. Dashboard Owner & Kasir

### 5.1 Layout Dashboard
```
┌──────────────────────────────────────────────────────────┐
│ Sidebar                  │ Konten Utama                  │
│ - Dashboard              │  Kartu ringkasan:             │
│ - Pesanan                │  [Omzet Hari Ini] [Omzet     │
│ - Buat Pesanan (POS)     │   Bulan Ini] [Pesanan        │
│ - Produk (lihat stok)    │   Pending] [Stok Menipis]    │
│                          │                               │
│ [Owner saja]             │  Grafik Tren Penjualan        │
│ - Kategori               │  Grafik Produk Terlaris       │
│ - Laporan Penjualan      │  Grafik Volume Pesanan        │
│ - Kupon                  │                               │
│ - Kelola Kasir           │                               │
│ - WhatsApp & Pengaturan  │                               │
└──────────────────────────────────────────────────────────┘
```

Kasir hanya melihat menu operasional; kartu omzet dan seluruh grafik hanya dirender untuk owner.

### 5.2 Definisi Grafik
| Grafik | Sumbu/Parameter | Sumber Data |
|---|---|---|
| Tren Penjualan | X: tanggal/bulan; Y: total omzet | `orders` dengan status `paid`, `diproses`, `dikirim`, `selesai` |
| Produk Terlaris | X: jumlah unit & omzet; Y: nama produk (top 10) | `order_items` join `orders` berstatus terbayar |
| Volume Pesanan | X: periode; Y: jumlah pesanan per status | `orders` |
| Ringkasan | Omzet harian/bulanan, jumlah pending, stok < ambang | `orders`, `products` |

### 5.3 Kriteria Grafik
- Data agregasi dihitung di server (query SQL) dan dikirim sebagai JSON ke Chart.js.
- Filter default: 30 hari terakhir.
- Zona waktu mengikuti `Asia/Jakarta`.
- Nilai uang diformat `Rp1.234.567`.
- Grafik dapat dimuat ulang tanpa refresh halaman (AJAX).

---

## 6. Pesanan Manual (POS) & Pembatalan

### 6.1 Alur Pesanan POS
```
Kasir membuka menu Buat Pesanan
        │
        ▼
Pilih produk + varian + jumlah, isi personalisasi bila wajib
        │
        ▼
Isi nama pembeli, nomor WA (opsional), pilih ambil di toko / kirim
        │
        ▼
Pilih metode bayar
        ├── Tunai  → isi uang diterima → validasi ≥ total → pesanan `paid`,
        │            stok berkurang, kembalian dihitung, struk 80mm auto-print
        └── QRIS   → pesanan `pending` + tautan pembayaran untuk pembeli
```

Setelah transaksi, kasir dapat mencetak label kirim 100×150mm dari detail
pesanan untuk pesanan berstatus `delivery` (bukan ambil di toko). Label
memuat penerima, alamat, nomor pesanan, jumlah item, dan catatan tanpa harga.
Notifikasi WhatsApp hanya dikirim setelah pesanan `paid`; kasir dapat mengirim
ulang struk secara manual dari detail pesanan.

### 6.2 Aturan Pembatalan
| Aturan | Detail |
|---|---|
| Batas kasir | Status `pending`, `paid`, atau `diproses` |
| Batas owner | Semua status kecuali `batal` |
| Alasan | Wajib diisi (maks. 500 karakter) |
| Stok | Dikembalikan bila pesanan sudah masuk status pendapatan (`paid` ke atas) |
| Audit | Pelaku, waktu, alasan, dan perubahan status disimpan |
| Refund | Dilakukan manual di luar sistem; sistem hanya mencatat pembatalan |

### 6.3 Status Pesanan
| Status | Arti |
|---|---|
| `pending` | Menunggu pembayaran (online/QRIS) |
| `paid` | Pembayaran diterima, stok berkurang |
| `diproses` | Pesanan sedang dikerjakan |
| `dikirim` | Pesanan dalam pengiriman |
| `selesai` | Pesanan diterima pembeli |
| `batal` | Dibatalkan; stok dikembalikan bila sudah lunas |

---

## 7. User Stories & Acceptance Criteria

### US-01 — Melihat katalog
**Sebagai** pengunjung, **saya ingin** melihat daftar produk berdasarkan kategori, **agar** mudah menemukan aksesoris yang dicari.

```gherkin
Scenario: Menampilkan daftar produk
  Given pengunjung membuka halaman katalog
  When halaman dimuat
  Then produk tampil dengan foto, nama, harga, dan status stok
  And produk dengan stok 0 berlabel "Habis"

Scenario: Filter kategori
  Given pengunjung berada di halaman katalog
  When pengunjung memilih kategori "Piala"
  Then hanya produk kategori Piala yang ditampilkan
```

### US-02 — Personalisasi produk
**Sebagai** pembeli, **saya ingin** menambahkan ukiran nama, **agar** produk sesuai untuk hadiah.

```gherkin
Scenario: Mengisi personalisasi wajib
  Given pembeli membuka detail produk kategori "Piala"
  When pembeli menambah ke keranjang tanpa mengisi teks personalisasi
  Then sistem menampilkan pesan "Teks personalisasi wajib diisi"
  And produk tidak ditambahkan ke keranjang

Scenario: Personalisasi tersimpan
  Given pembeli mengisi teks personalisasi "Untuk Rina — Wisuda 2026"
  When pembeli menambahkan produk ke keranjang dan checkout
  Then teks personalisasi tersimpan pada item pesanan
  And teks tampil di detail pesanan admin dan pembeli
```

### US-03 — Checkout dan pembayaran
**Sebagai** pembeli, **saya ingin** membayar online, **agar** pesanan langsung diproses.

```gherkin
Scenario: Checkout berhasil
  Given pembeli memiliki item di keranjang dan alamat tersimpan
  And pembeli mengisi nomor WhatsApp yang valid
  When pembeli menyelesaikan checkout
  Then pesanan dibuat dengan status "pending"
  And pembeli diarahkan ke halaman pembayaran Midtrans

Scenario: Pembayaran sukses
  Given pembeli menyelesaikan pembayaran di Midtrans
  When webhook menerima status "settlement" dengan signature valid
  Then status pesanan berubah menjadi "paid"
  And stok produk berkurang sesuai jumlah yang dibeli
```

### US-04 — Notifikasi WhatsApp
**Sebagai** pembeli, **saya ingin** menerima konfirmasi WhatsApp, **agar** yakin pembayaran diterima. **Sebagai** admin, **saya ingin** diberi tahu pesanan baru.

```gherkin
Scenario: Notifikasi WA setelah pembayaran sukses
  Given pesanan berstatus "pending"
  When webhook Midtrans mengubah status menjadi "paid"
  Then job notifikasi WhatsApp dijalankan
  And pembeli menerima pesan berisi nomor pesanan, item, personalisasi, total, metode bayar, dan tautan struk
  And transaksi tunai menampilkan uang diterima dan kembalian
  And admin menerima pesan berisi nomor pesanan, nama pembeli, total, jumlah item, dan pemenuhan
  And setiap pengiriman tercatat di whatsapp_logs dengan status "sent" dan jenis "payment_receipt"

Scenario: Kasir mengirim ulang struk
  Given pesanan sudah "paid"
  When kasir menekan "Kirim ulang struk"
  Then pesan WhatsApp berisi tautan struk dikirim lagi ke pembeli
  And log baru tercatat dengan jenis "manual_resend"

Scenario: Pesanan belum lunas
  Given pesanan berstatus "pending" atau "batal"
  When job notifikasi dijalankan
  Then tidak ada pesan WhatsApp yang dikirim

Scenario: Pengiriman gagal
  Given provider Fonnte mengembalikan error
  When job notifikasi dijalankan
  Then sistem mencoba ulang maksimal 3 kali
  And setelah gagal, status log menjadi "failed" dan email fallback dikirim
```

### US-05 — Dashboard grafik
**Sebagai** admin, **saya ingin** melihat grafik penjualan, **agar** dapat memantau performa toko.

```gherkin
Scenario: Melihat tren penjualan
  Given admin login dan membuka dashboard
  When admin memilih filter "30 hari terakhir"
  Then grafik menampilkan omzet per hari selama 30 hari
  And hanya pesanan berstatus terbayar yang dihitung

Scenario: Melihat produk terlaris
  Given terdapat data penjualan
  When admin membuka grafik produk terlaris
  Then ditampilkan 10 produk dengan unit terjual terbanyak
```

### US-06 — Checkout tamu & pelacakan pesanan
**Sebagai** pembeli, **saya ingin** checkout tanpa membuat akun dan melacak pesanan, **agar** proses belanja cepat dan status pesanan jelas.

```gherkin
Scenario: Checkout sebagai tamu
  Given pengunjung memiliki item di keranjang
  When pengunjung mengisi nama, nomor WhatsApp, dan alamat lalu checkout
  Then pesanan dibuat dengan status "pending" tanpa akun pengguna
  And pengunjung diarahkan ke tautan pembayaran bertanda tangan

Scenario: Melacak pesanan
  Given pembeli mengetahui nomor pesanan dan nomor WhatsApp
  When pembeli membuka halaman Lacak Pesanan dan mengisi keduanya
  Then status, item, total, dan riwayat status pesanan ditampilkan
  And nomor WhatsApp yang tidak cocok ditolak

Scenario: Tautan pesanan tanpa tanda tangan
  Given pengunjung membuka tautan pembayaran tanpa signature yang valid
  Then sistem menampilkan halaman 403
```

### US-07 — Role akses & operasional kasir
**Sebagai** pemilik toko, **saya ingin** membatasi akses kasir, **agar** data laporan dan pengaturan hanya dikelola owner.

```gherkin
Scenario: Kasir tidak dapat membuka laporan
  Given pengguna login dengan role "kasir"
  When pengguna membuka URL laporan penjualan
  Then sistem menampilkan halaman 403 Forbidden

Scenario: Kasir membuat pesanan tunai
  Given kasir membuka menu Buat Pesanan
  When kasir memilih produk dan metode bayar "Tunai"
  Then pesanan tercatat sebagai POS dengan status "paid"
  And stok produk berkurang

Scenario: Kasir membatalkan pesanan
  Given pesanan berstatus "diproses"
  When kasir mengirim pembatalan dengan alasan
  Then pesanan menjadi "batal" dan stok dikembalikan
  And perubahan tercatat di riwayat status

Scenario: Kasir tidak dapat membatalkan pesanan terkirim
  Given pesanan berstatus "dikirim"
  When kasir mencoba membatalkan
  Then sistem menampilkan 403 dan status pesanan tidak berubah
```

---

## 8. Kebutuhan Non-Fungsional

### 8.1 Performa
- Waktu muat halaman katalog < 3 detik pada koneksi 4G.
- Respons API < 500 ms untuk 95% request (di luar proses pembayaran).
- Gambar produk dikompresi dan disajikan dalam ukuran responsif (thumbnail + besar).
- Query grafik menggunakan indeks pada `orders.created_at`, `orders.status`, dan `order_items.product_id`.

### 8.2 Keamanan
- Password di-hash dengan bcrypt/Argon2.
- Semua form menggunakan validasi server-side dan proteksi CSRF.
- Webhook Midtrans diverifikasi menggunakan signature key (`SHA512(order_id + status_code + gross_amount + server_key)`).
- Webhook Fonnte/endpoint internal dilindungi token.
- Kredensial (Midtrans server key, Fonnte API key) disimpan di `.env`, tidak di repository.
- Role-based access control (middleware) untuk seluruh route admin; menu owner-only dipisahkan.
- Tautan pembayaran dan halaman selesai untuk tamu menggunakan signed URL berbatas waktu (7 hari).
- Rate limiting pada login, checkout, dan pelacakan pesanan.

### 8.3 Keandalan
- Queue worker untuk pengiriman WhatsApp dengan retry 3x (backoff 1, 5, 15 menit).
- Log aplikasi harian dan log khusus `whatsapp_logs`.
- Backup database harian (retensi 14 hari).
- Penanganan webhook idempotent (pesanan yang sama tidak diproses dua kali).

### 8.4 Usability
- Desain responsif (mobile, tablet, desktop).
- Bahasa antarmuka: Indonesia.
- Form checkout menampilkan ringkasan biaya secara jelas sebelum bayar.
- Pesan error dalam bahasa Indonesia yang mudah dipahami.

### 8.5 Kompatibilitas
- Browser: Chrome, Safari, Edge, Firefox versi terbaru (2 versi terakhir).
- Checkout dan pelacakan pesanan dapat digunakan tanpa aplikasi tambahan.

### 8.6 Legal & Privasi
- Menyimpan data pribadi (nama, alamat, nomor WA) hanya untuk keperluan pesanan.
- Halaman kebijakan privasi dan syarat & ketentuan minimal.
- Data pembayaran tidak disimpan di server; diproses oleh Midtrans.

---

## 9. Model Data

### 9.1 Entity Relationship (ringkas)
```
users 1─* addresses
users 1─* orders
users 1─* attendances
categories 1─* products
products 1─* product_images
products 1─* product_variants
products 1─* order_items
orders 1─* order_items
orders 1─1 payments
orders 1─* whatsapp_logs
coupons 1─* orders
```

### 9.2 Tabel

**users**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | |
| phone | VARCHAR(20) | Nomor WhatsApp |
| password | VARCHAR(255) | Hash |
| role | VARCHAR(20) | `owner`, `kasir`, atau `customer` (akun lama). Default `customer` |
| is_active | BOOLEAN | Default true |
| created_at, updated_at | TIMESTAMP | |

**addresses**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| user_id | FK users | |
| recipient_name | VARCHAR(100) | |
| phone | VARCHAR(20) | |
| address | TEXT | |
| city | VARCHAR(100) | |
| postal_code | VARCHAR(10) | |
| is_default | BOOLEAN | |
| created_at, updated_at | TIMESTAMP | |

**categories**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(100) | |
| slug | VARCHAR(120) UNIQUE | |
| is_active | BOOLEAN | |
| created_at, updated_at | TIMESTAMP | |

**products**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| category_id | FK categories | |
| name | VARCHAR(150) | |
| slug | VARCHAR(180) UNIQUE | |
| description | TEXT | |
| price | DECIMAL(12,2) | |
| stock | INT | |
| requires_personalization | BOOLEAN | Default false |
| is_active | BOOLEAN | Default true |
| low_stock_threshold | INT | Default 5 |
| created_at, updated_at | TIMESTAMP | |

**product_images**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| product_id | FK products | |
| path | VARCHAR(255) | Lokasi file |
| is_primary | BOOLEAN | |
| sort_order | INT | |
| created_at, updated_at | TIMESTAMP | |

**product_variants**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| product_id | FK products | |
| name | VARCHAR(100) | Mis. "Merah / 10 cm" |
| price_adjustment | DECIMAL(12,2) | Default 0 |
| stock | INT | |
| created_at, updated_at | TIMESTAMP | |

**orders**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| order_number | VARCHAR(30) UNIQUE | Mis. MVA-20261022-0001 |
| user_id | FK users NULL | Kosong untuk pesanan tamu/POS |
| address_id | FK addresses NULL | |
| customer_name | VARCHAR(100) | Snapshot |
| customer_phone | VARCHAR(20) NULL | Nomor WA pembeli; boleh kosong untuk walk-in |
| customer_address | TEXT | Snapshot |
| coupon_id | FK coupons NULL | |
| subtotal | DECIMAL(12,2) | |
| discount | DECIMAL(12,2) | Default 0 |
| shipping_cost | DECIMAL(12,2) | Ongkir flat |
| total | DECIMAL(12,2) | |
| status | ENUM('pending','paid','diproses','dikirim','selesai','batal') | |
| channel | VARCHAR(10) | `online` atau `pos`, default `online` |
| fulfillment | VARCHAR(10) | `delivery` atau `pickup`, default `delivery` |
| cash_received | DECIMAL(12,2) NULL | Uang tunai diterima (POS tunai) |
| change_amount | DECIMAL(12,2) NULL | Kembalian = uang diterima − total |
| created_by | FK users NULL | Kasir pembuat pesanan POS |
| cancelled_by | FK users NULL | Pelaku pembatalan |
| cancelled_at | TIMESTAMP NULL | |
| cancel_reason | TEXT NULL | |
| paid_at | TIMESTAMP NULL | |
| notes | TEXT NULL | |
| created_at, updated_at | TIMESTAMP | |
| INDEX(customer_phone) | | Mempercepat pelacakan pesanan |

**order_items**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| order_id | FK orders | |
| product_id | FK products | |
| product_name | VARCHAR(150) | Snapshot |
| variant_id | FK product_variants NULL | |
| variant_name | VARCHAR(100) NULL | Snapshot |
| price | DECIMAL(12,2) | |
| quantity | INT | |
| personalization_text | VARCHAR(60) NULL | Teks ukiran/nama |
| personalization_notes | VARCHAR(200) NULL | Catatan tambahan |
| subtotal | DECIMAL(12,2) | |
| created_at, updated_at | TIMESTAMP | |

**payments**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| order_id | FK orders | |
| transaction_id | VARCHAR(100) NULL | Dari Midtrans |
| payment_type | VARCHAR(50) NULL | qris, bank_transfer, ewallet |
| gross_amount | DECIMAL(12,2) | |
| status | VARCHAR(30) | pending, settlement, capture, expire, deny |
| raw_response | JSON NULL | Payload webhook |
| paid_at | TIMESTAMP NULL | |
| created_at, updated_at | TIMESTAMP | |

**whatsapp_logs**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| order_id | FK orders NULL | |
| recipient | VARCHAR(20) | Nomor tujuan |
| recipient_type | ENUM('customer','admin') | |
| message_type | VARCHAR(30) | `payment_receipt` (otomatis) atau `manual_resend` |
| message | TEXT | |
| status | ENUM('queued','sent','failed') | |
| provider_response | TEXT NULL | |
| attempts | INT | Default 0 |
| sent_at | TIMESTAMP NULL | |
| created_at, updated_at | TIMESTAMP | |

**coupons**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| code | VARCHAR(30) UNIQUE | |
| type | ENUM('percentage','fixed') | |
| value | DECIMAL(12,2) | |
| min_purchase | DECIMAL(12,2) | |
| max_uses | INT NULL | |
| used_count | INT | Default 0 |
| starts_at, expires_at | TIMESTAMP NULL | |
| is_active | BOOLEAN | |
| created_at, updated_at | TIMESTAMP | |

**order_status_logs**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| order_id | FK orders | |
| user_id | FK users NULL | Pelaku; null untuk sistem |
| from_status | VARCHAR(20) NULL | |
| to_status | VARCHAR(20) | |
| note | VARCHAR(255) NULL | Catatan/alasan |
| created_at, updated_at | TIMESTAMP | |
| INDEX(order_id, created_at) | | Riwayat kronologis |

**settings**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| key | VARCHAR(100) UNIQUE | Mis. `store_name`, `shipping_flat` |
| value | TEXT | |
| created_at, updated_at | TIMESTAMP | |

---

## 10. Integrasi Eksternal

### 10.1 Midtrans
| Item | Detail |
|---|---|
| Metode | Snap (redirect/embedded) + HTTP Notification (webhook) |
| Endpoint webhook | `POST /webhooks/midtrans` |
| Verifikasi | `SHA512(order_id + status_code + gross_amount + server_key)` dibandingkan `signature_key` |
| Status yang ditangani | `settlement`, `capture`, `pending`, `expire`, `deny`, `cancel` |
| Idempotensi | Proses hanya jika status transaksi berubah dan belum pernah diproses |
| Environment | Sandbox saat development, production saat live |
| Env vars | `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` |

### 10.2 Fonnte (WhatsApp)
| Item | Detail |
|---|---|
| Metode | REST API `POST https://api.fonnte.com/send` |
| Autentikasi | Header `Authorization: <token>` |
| Payload | `target` (nomor), `message` (teks), `delay` (opsional) |
| Pemicu | Job `SendOrderNotification` setelah event `OrderPaid`; dapat dipicu ulang manual dengan `resend=true` |
| Retry | 3x backoff (1, 5, 15 menit) |
| Logging | Simpan request/response ringkas ke `whatsapp_logs` beserta `message_type` (`payment_receipt`/`manual_resend`) |
| Env vars | `WA_PROVIDER=fonnte`, `WA_API_KEY`, `WA_ADMIN_NUMBER`, `WA_SENDER` |
| Risiko | Provider tidak resmi; nomor berpotensi diblokir jika spam. Mitigasi: kirim hanya transaksi, hindari broadcast masif, siap migrasi ke WhatsApp Cloud API |

### 10.3 Template WhatsApp

**Ke pembeli (setelah pembayaran sukses):**
```
Halo {{nama}}, pembayaran pesanan {{order_number}} di Multiverse Aksesoris
sudah kami terima.

Detail pesanan:
{{daftar_item}}
Total: Rp{{total}}
Pembayaran: {{metode}}
Tunai diterima: Rp{{cash_received}}   (khusus tunai)
Kembalian: Rp{{change_amount}}        (khusus tunai)
Pemenuhan: {{dikirim/ambil_di_toko}}

Struk pembayaran (cetak/simpan): {{url_struk_signed_30_hari}}

Pesanan akan segera kami proses. Terima kasih sudah berbelanja!
```

**Ke admin (setelah pembayaran sukses):**
```
Pesanan baru LUNAS {{order_number}}
Pembeli: {{nama}} ({{phone}})
Jumlah item: {{jumlah_item}}
Total: Rp{{total}}
Pembayaran: {{metode}}
Pemenuhan: {{dikirim/ambil_di_toko}}
Tunai diterima: Rp{{cash_received}} | Kembalian: Rp{{change_amount}}

Buka pesanan: {{url_admin_order}}
```

### 10.4 Email (fallback)
- SMTP untuk email konfirmasi pesanan dan fallback notifikasi WA.
- Env vars: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`.

---

## 11. Status Pesanan

### 11.1 State Diagram
```
                 ┌──────────┐
                 │ pending  │
                 └────┬─────┘
          bayar sukses│      gagal/expire
                      ▼            │
                 ┌──────────┐      ▼
                 │   paid   │  ┌────────┐
                 └────┬─────┘  │ batal  │
                      │        └────────┘
                      ▼
                 ┌──────────┐
                 │ diproses │
                 └────┬─────┘
                      ▼
                 ┌──────────┐
                 │ dikirim  │
                 └────┬─────┘
                      ▼
                 ┌──────────┐
                 │ selesai  │
                 └──────────┘
```

### 11.2 Aturan Transisi
| Dari | Ke | Pemicu | Aktor |
|---|---|---|---|
| pending | paid | Webhook Midtrans settlement/capture | Sistem |
| pending | batal | Webhook expire/deny/cancel atau dibatalkan admin | Sistem/Admin |
| paid | diproses | Admin menandai pesanan mulai diproses | Admin |
| diproses | dikirim | Admin input nomor resi/pengiriman | Admin |
| dikirim | selesai | Admin menandai selesai | Admin |
| paid/diproses | batal | Refund/disepakati admin (stok dikembalikan) | Admin |

### 11.3 Efek Samping
- `pending → paid`: kurangi stok, buat `payment`, kirim WA, tampilkan di grafik.
- `pending → batal`: tidak ada perubahan stok (stok belum dikurangi).
- `paid/diproses → batal`: kembalikan stok, catat alasan pembatalan.
- Semua perubahan status dicatat di log aktivitas.

---

## 12. Risiko & Asumsi

| ID | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R-01 | Nomor WA diblokir karena provider Fonnte tidak resmi | Notifikasi gagal | Kirim hanya pesan transaksional, retry + fallback email, rencana migrasi ke Cloud API saat volume naik |
| R-02 | Pesanan tamu sulit dilacak jika nomor pesanan/WA salah | Pembeli tidak bisa cek status | Validasi format nomor, kirim nomor pesanan via WA, pencarian berbasis nomor pesanan + WA |
| R-03 | Webhook Midtrans tidak diterima | Pesanan tetap pending | Sediakan halaman cek status manual + sinkronisasi status via API Midtrans |
| R-04 | Stok tidak akurat karena penjualan offline | Overselling | Kasir mencatat penjualan via POS; validasi stok saat checkout; penanda stok menipis |
| R-05 | Personalisasi salah tulis oleh pembeli | Komplain/retur | Tampilkan preview + konfirmasi sebelum bayar; teks tercatat di WA dan detail pesanan |
| R-06 | Ongkir flat tidak sesuai ongkos kirim riil | Kerugian biaya kirim | Owner menetapkan tarif konservatif; integrasi kurir di fase 2 |
| R-07 | Beban grafik memperlambat dashboard | UX buruk | Agregasi server + cache hasil query 5 menit + indeks database |
| R-08 | Kasir menyalahgunakan pembatalan | Kerugian stok/omzet | Batasi status yang bisa dibatalkan + audit trail pelaku dan alasan |

### 12.1 Asumsi Teknis
- Server mendukung PHP 8.2+, MySQL 8, queue worker (database/redis), dan HTTPS.
- Storage gambar menggunakan local disk atau S3-compatible (dapat dikonfigurasi).
- Zona waktu aplikasi `Asia/Jakarta`.

---

## 13. Roadmap

### Fase 1 — MVP (sesuai dokumen ini)
Katalog, personalisasi, checkout tamu + pelacakan pesanan, keranjang, Midtrans, notifikasi WA setelah pembayaran + tautan struk, struk thermal 80mm (online & POS), label kirim internal 100×150mm, kirim ulang struk, dashboard owner/kasir, grafik penjualan, POS, pembatalan + audit trail, role owner/kasir.

### Fase 2 — Peningkatan
- Notifikasi WA untuk setiap perubahan status (diproses, dikirim, selesai) + template yang dapat diedit owner.
- Ulasan produk dan rating.
- Wishlist.
- Integrasi ongkir otomatis (RajaOngkir).
- Akun pembeli opsional dengan poin loyalitas.
- Kupon dan program loyalitas/poin.
- Migrasi ke WhatsApp Cloud API resmi.
- Laporan keuangan lanjutan (laba kotor, produk tidak laku).

### Fase 3 — Skala
- Aplikasi mobile (PWA/native).
- Multi-gudang dan multi-kurir.
- Rekomendasi produk dan analitik lanjutan.
- Integrasi akuntansi.

---

## 14. Open Questions

| No | Pertanyaan | Pemilik | Target Keputusan |
|---|---|---|---|
| Q-01 | Nominal ongkir flat yang dipakai? Apakah berbeda per wilayah? | Pemilik toko | Sebelum development |
| Q-02 | Kebijakan retur/penukaran, khususnya produk personalisasi? | Pemilik toko | Sebelum launch |
| Q-03 | Logo, palet warna, dan identitas visual Multiverse Aksesoris? | Pemilik toko | Sebelum UI design |
| Q-04 | Nomor WhatsApp admin untuk notifikasi? | Pemilik toko | Sebelum setup `.env` |
| Q-05 | Apakah kasir boleh melihat nominal omzet harian? (Saat ini hanya owner.) | Pemilik toko | Sebelum launch |
| Q-06 | Apakah pembatalan oleh kasir perlu persetujuan owner? | Pemilik toko | Sebelum launch |
| Q-07 | Metode pengiriman yang tersedia (JNE, J&T, SiCepat, kurir instan)? | Pemilik toko | Fase 2 |
| Q-08 | Apakah diperlukan invoice PDF? | Pemilik toko | Fase 2 |

---

## Lampiran A — Daftar Prioritas (MoSCoW)

| Prioritas | Fitur |
|---|---|
| Must | Katalog, detail produk, personalisasi, checkout tamu, pelacakan pesanan, keranjang, Midtrans, WA setelah pembayaran, dashboard owner/kasir, grafik penjualan, POS, pembatalan + audit trail, role owner/kasir |
| Should | Kupon, fallback email, tes kirim WA, filter/urut katalog, kelola kasir, pengaturan toko |
| Could | Preview personalisasi real-time, export laporan lanjutan, invoice PDF |
| Won't (MVP) | Marketplace, kurir otomatis, mobile app, ulasan, wishlist, akun pembeli wajib |

## Lampiran B — Environment Variables (ringkas)

```env
APP_NAME="Multiverse Aksesoris"
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://multiverseaksesoris.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=multiverse_aksesoris
DB_USERNAME=
DB_PASSWORD=

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

WA_PROVIDER=fonnte
WA_API_KEY=
WA_ADMIN_NUMBER=
WA_SENDER=

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
```

---

*Dokumen ini menjadi acuan tunggal (single source of truth) untuk pengembangan MVP Multiverse Aksesoris. Perubahan lingkup harus dicatat sebagai revisi versi baru.*
