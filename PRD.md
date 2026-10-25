# Product Requirements Document (PRD)

## Multiverse Aksesoris — Toko Online Aksesoris

| Item | Keterangan |
|---|---|
| Nama Produk | Multiverse Aksesoris |
| Jenis Produk | Toko online (e-commerce) B2C |
| Versi Dokumen | 1.0 |
| Tanggal | 22 Oktober 2026 |
| Status | Final untuk MVP |
| Stack | Laravel 11 + MySQL 8, Midtrans, Fonnte (WhatsApp), Chart.js |
| Pemilik Dokumen | Pemilik Toko / Product Owner |

---

## 1. Ringkasan & Tujuan Produk

### 1.1 Latar Belakang
Multiverse Aksesoris menjual produk aksesoris seperti gantungan kunci, boneka/plush, pigura, piala, dan produk sejenis. Saat ini penjualan masih dikelola secara manual sehingga pencatatan pesanan, konfirmasi pembayaran, pengelolaan stok, dan notifikasi ke pembeli memakan waktu serta rawan kesalahan.

### 1.2 Tujuan
Membangun website toko online yang memungkinkan pembeli melihat katalog, memesan produk (termasuk personalisasi), membayar secara online, dan menerima konfirmasi otomatis melalui WhatsApp; sekaligus memberi admin dashboard untuk mengelola produk, pesanan, laporan penjualan, dan absensi karyawan.

### 1.3 Metrik Keberhasilan (3 bulan pertama)
| Metrik | Target |
|---|---|
| Conversion rate (kunjungan → pesanan lunas) | ≥ 2% |
| Pesanan lunas per bulan | ≥ 100 |
| Waktu konfirmasi WA setelah pembayaran sukses | < 1 menit |
| Waktu admin memproses pesanan baru | < 5 menit |
| Kesalahan pencatatan stok | 0 (stok berkurang otomatis) |
| Kehadiran karyawan tercatat lengkap | ≥ 95% hari kerja |

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
3. Registrasi, login, profil, dan alamat pengiriman.
4. Keranjang belanja dan checkout.
5. Pembayaran online melalui Midtrans (QRIS, Virtual Account, e-wallet).
6. Notifikasi WhatsApp otomatis via Fonnte setelah pembayaran sukses (pembeli dan admin).
7. Dashboard admin: CRUD produk & foto, kelola pesanan, kelola stok, laporan penjualan.
8. Grafik penjualan: tren penjualan, produk terlaris, volume pesanan.
9. Absensi karyawan dengan scan QR di toko.
10. Role akses: `admin` dan `karyawan`.

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
- Nomor WhatsApp pembeli wajib diisi saat checkout.
- Nama toko ditulis **Multiverse Aksesoris**.

---

## 3. Persona & User Journey

### 3.1 Persona

**P1 — Pembeli Hadiah (B2C)**
- Usia 18–35, membeli piala/pigura/gantungan kunci untuk hadiah ulang tahun, wisuda, atau kenang-kenangan.
- Butuh personalisasi nama/teks dan ingin cepat tahu pesanan diproses.
- Sering mengakses dari ponsel.

**P2 — Admin Toko**
- Mengelola produk, stok, pesanan, dan laporan.
- Ingin melihat performa penjualan sekilas melalui grafik.
- Perlu memantau kehadiran karyawan.

**P3 — Karyawan**
- Staf toko yang wajib absen masuk/pulang.
- Hanya membutuhkan akses halaman absensi dan riwayat kehadiran pribadi.

### 3.2 User Journey — Pembeli
```
Buka website → Jelajahi katalog → Buka detail produk
→ Pilih varian + isi personalisasi → Tambah ke keranjang
→ Checkout (isi alamat, no. WA, pilih ongkir)
→ Bayar via Midtrans → Pembayaran sukses
→ Terima notifikasi WhatsApp → Pesanan diproses admin
→ Pesanan dikirim → Pesanan selesai
```

### 3.3 User Journey — Admin
```
Login → Dashboard (grafik penjualan & ringkasan)
→ Kelola produk/stok → Proses pesanan masuk
→ Update status pesanan → Pantau absensi karyawan
```

### 3.4 User Journey — Karyawan
```
Login → Halaman Absensi → Scan QR toko
→ Sistem mencatat jam masuk → (sore) scan QR lagi untuk jam pulang
→ Lihat riwayat kehadiran pribadi
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

### 4.3 Akun Pengguna
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-13 | Registrasi dengan nama, email, nomor WhatsApp, dan password. | Must |
| F-14 | Login/logout dan fitur lupa password via email. | Must |
| F-15 | Pengguna dapat menyimpan alamat pengiriman (nama penerima, telepon, alamat, kota, kode pos). | Must |
| F-16 | Riwayat pesanan beserta status dan detail pembayaran. | Must |
| F-17 | Pengguna dapat mengubah profil dan nomor WhatsApp. | Should |

### 4.4 Keranjang & Checkout
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-18 | Tambah, ubah jumlah, dan hapus item dari keranjang. | Must |
| F-19 | Keranjang menyimpan varian dan teks personalisasi per item. | Must |
| F-20 | Checkout: pilih alamat, konfirmasi nomor WhatsApp, pilih ongkir flat, ringkasan total. | Must |
| F-21 | Validasi stok ulang saat checkout; tolak jika stok tidak cukup. | Must |
| F-22 | Kode kupon diskon (persentase/nominal) dikelola admin. | Should |
| F-23 | Setelah checkout, pesanan dibuat dengan status `pending` dan pembeli diarahkan ke halaman pembayaran Midtrans. | Must |

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
| F-32 | Template pembeli: nomor pesanan, daftar item + personalisasi, total, status pembayaran diterima. | Must |
| F-33 | Template admin: nomor pesanan, nama pembeli, total, jumlah item, tautan dashboard. | Must |
| F-34 | Pengiriman melalui queue job agar tidak memperlambat webhook; retry 3x dengan backoff. | Must |
| F-35 | Setiap pengiriman dicatat di `whatsapp_logs` (penerima, status, response provider, waktu). | Must |
| F-36 | Fallback email jika pengiriman WA gagal setelah retry. | Should |
| F-37 | Tombol "Tes Kirim WA" di dashboard admin. | Should |

### 4.7 Dashboard Admin
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-38 | CRUD produk: nama, kategori, deskripsi, harga, stok, status personalisasi, varian. | Must |
| F-39 | Upload beberapa foto produk; foto utama dapat dipilih; hapus foto. | Must |
| F-40 | Daftar pesanan dengan filter status, tanggal, dan pencarian nomor pesanan. | Must |
| F-41 | Detail pesanan: data pembeli, alamat, item, personalisasi, pembayaran, tombol ubah status. | Must |
| F-42 | Kelola stok dan penanda stok menipis (ambang batas dapat diatur). | Must |
| F-43 | Laporan penjualan dengan filter periode dan export CSV. | Must |
| F-44 | Ringkasan dashboard: omzet hari ini/bulan ini, pesanan pending, stok menipis. | Must |
| F-45 | Kelola ongkir flat dan kode kupon. | Should |
| F-46 | Manajemen pengguna: daftar pengguna dan role. | Should |
| F-47 | Pengaturan toko: nama, logo, nomor admin WA, jam kerja. | Should |

### 4.8 Grafik Penjualan
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-48 | Grafik tren penjualan (garis) dengan filter harian/bulanan/tahunan. | Must |
| F-49 | Grafik produk terlaris (bar horizontal, top 10 berdasarkan unit dan omzet). | Must |
| F-50 | Grafik volume pesanan per status dan per periode. | Must |
| F-51 | Data grafik hanya dari pesanan berstatus `paid` ke atas (tidak termasuk `pending`/`batal`). | Must |
| F-52 | Grafik responsif dan menampilkan tooltip nilai Rupiah. | Should |

### 4.9 Absensi Karyawan
| ID | Kebutuhan | Prioritas |
|---|---|---|
| F-53 | Role `admin` dan `karyawan` dengan hak akses berbeda. | Must |
| F-54 | Admin dapat menampilkan QR absensi di dashboard; QR berisi token bertanda tangan (HMAC) dengan masa berlaku. | Must |
| F-55 | Karyawan login lalu membuka halaman Absensi dan memindai QR menggunakan kamera ponsel. | Must |
| F-56 | Scan pertama pada hari kerja mencatat `check_in`; scan berikutnya mencatat `check_out` setelah konfirmasi. | Must |
| F-57 | Waktu absensi menggunakan waktu server; IP dan user agent dicatat. | Must |
| F-58 | Status kehadiran otomatis: `hadir`, `telat`, `tidak_hadir` berdasarkan jam kerja dan toleransi. | Must |
| F-59 | Admin dapat mengatur jam masuk, jam pulang, dan toleransi telat. | Must |
| F-60 | Admin melihat rekap absensi harian/bulanan dan dapat export CSV. | Must |
| F-61 | Karyawan melihat riwayat kehadiran pribadi. | Must |
| F-62 | QR absensi memiliki masa berlaku (rotasi token) agar tidak dapat digunakan ulang di luar periode. | Must |

---

## 5. Dashboard Admin & Grafik Penjualan

### 5.1 Layout Dashboard
```
┌──────────────────────────────────────────────────────────┐
│ Sidebar                  │ Konten Utama                  │
│ - Dashboard              │  Kartu ringkasan:             │
│ - Produk                 │  [Omzet Hari Ini] [Omzet     │
│ - Pesanan                │   Bulan Ini] [Pesanan        │
│ - Laporan                │   Pending] [Stok Menipis]    │
│ - Absensi                │                               │
│ - Karyawan               │  Grafik Tren Penjualan        │
│ - Pengaturan             │  Grafik Produk Terlaris       │
│                          │  Grafik Volume Pesanan        │
└──────────────────────────────────────────────────────────┘
```

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

## 6. Absensi Karyawan (Scan QR)

### 6.1 Alur QR
```
Admin membuka menu Absensi > Tampilkan QR
        │
        ▼
Server membuat token: HMAC(order_id/acak, secret) + expires_at
        │
        ▼
QR ditampilkan di layar dashboard (berlaku mis. 60 detik,
dapat diperpanjang otomatis)
        │
        ▼
Karyawan login → halaman Absensi → scan QR dengan kamera
        │
        ▼
Server validasi: token valid, belum kedaluwarsa, user login & role karyawan
        │
        ▼
Catat check_in / check_out + IP + user agent
        │
        ▼
Tampilkan konfirmasi "Absen masuk berhasil pukul 08:02 (Hadir)"
```

### 6.2 Aturan Absensi
| Aturan | Detail |
|---|---|
| Jam masuk default | 08:00 |
| Jam pulang default | 17:00 |
| Toleransi telat | 15 menit (dapat diubah admin) |
| Status | `hadir` (≤ batas toleransi), `telat` (> toleransi), `tidak_hadir` (tidak ada scan sampai akhir hari) |
| Scan ganda | Scan check-in kedua pada hari yang sama diabaikan/dialihkan sebagai check-out dengan konfirmasi |
| Masa berlaku token | 60 detik, rotasi otomatis; token lama tidak valid |
| Hari kerja | Senin–Sabtu (dapat diatur admin) |

### 6.3 Rekap Absensi Admin
- Tampilan tabel per bulan: nama karyawan × tanggal, dengan kode H/T/A.
- Ringkasan per karyawan: total hadir, telat, tidak hadir.
- Filter rentang tanggal dan export CSV.

### 6.4 Batasan & Mitigasi
- QR dapat difoto/dibagikan. Mitigasi: token berumur pendek + validasi akun login + pencatatan IP.
- Roadmap fase 2: tambah selfie dan validasi GPS.

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
  And pembeli menerima pesan berisi nomor pesanan, item, personalisasi, dan total
  And admin menerima pesan berisi nomor pesanan, nama pembeli, total, dan jumlah item
  And setiap pengiriman tercatat di whatsapp_logs dengan status "sent"

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

### US-06 — Absensi karyawan
**Sebagai** karyawan, **saya ingin** absen dengan scan QR, **agar** kehadiran tercatat tanpa mengisi form.

```gherkin
Scenario: Absen masuk berhasil
  Given karyawan sudah login
  And admin menampilkan QR absensi yang valid
  When karyawan memindai QR
  Then sistem mencatat jam masuk dengan waktu server
  And status kehadiran dihitung "hadir" atau "telat" sesuai toleransi

Scenario: QR kedaluwarsa
  Given token QR sudah melewati masa berlaku
  When karyawan memindai QR tersebut
  Then sistem menolak dengan pesan "QR kedaluwarsa, minta admin memperbarui QR"
  And tidak ada data absensi yang tercatat

Scenario: Rekap absensi
  Given admin membuka menu Absensi
  When admin memilih bulan berjalan
  Then tampil tabel kehadiran seluruh karyawan
  And admin dapat mengunduh rekap dalam format CSV
```

### US-07 — Role akses
**Sebagai** pemilik toko, **saya ingin** membatasi akses, **agar** data penjualan hanya dilihat admin.

```gherkin
Scenario: Karyawan tidak dapat membuka laporan
  Given pengguna login dengan role "karyawan"
  When pengguna membuka URL laporan penjualan
  Then sistem menampilkan halaman 403 Forbidden
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
- Role-based access control (middleware) untuk seluruh route admin.
- Token QR absensi menggunakan HMAC-SHA256 dengan secret server dan masa berlaku pendek.
- Rate limiting pada login dan endpoint absensi.

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
- Scan QR memerlukan akses kamera browser pada ponsel (HTTPS wajib).

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
| role | ENUM('admin','karyawan','customer') | Default `customer` |
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
| user_id | FK users | |
| address_id | FK addresses | |
| customer_name | VARCHAR(100) | Snapshot |
| customer_phone | VARCHAR(20) | Nomor WA pembeli |
| customer_address | TEXT | Snapshot |
| coupon_id | FK coupons NULL | |
| subtotal | DECIMAL(12,2) | |
| discount | DECIMAL(12,2) | Default 0 |
| shipping_cost | DECIMAL(12,2) | Ongkir flat |
| total | DECIMAL(12,2) | |
| status | ENUM('pending','paid','diproses','dikirim','selesai','batal') | |
| paid_at | TIMESTAMP NULL | |
| notes | TEXT NULL | |
| created_at, updated_at | TIMESTAMP | |

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

**attendances**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| user_id | FK users | Karyawan |
| date | DATE | |
| check_in | TIME NULL | |
| check_out | TIME NULL | |
| status | ENUM('hadir','telat','tidak_hadir') | |
| ip_address | VARCHAR(45) NULL | |
| user_agent | VARCHAR(255) NULL | |
| created_at, updated_at | TIMESTAMP | |
| UNIQUE(user_id, date) | | Satu record per hari |

**attendance_settings**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| work_start | TIME | Default 08:00 |
| work_end | TIME | Default 17:00 |
| late_tolerance_minutes | INT | Default 15 |
| work_days | VARCHAR(20) | Mis. "1,2,3,4,5,6" (Senin–Sabtu) |
| created_at, updated_at | TIMESTAMP | |

**attendance_tokens**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | BIGINT PK | |
| token | VARCHAR(255) | Token acak |
| expires_at | TIMESTAMP | |
| created_by | FK users | Admin |
| created_at, updated_at | TIMESTAMP | |

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
| Pemicu | Job `SendOrderNotification` setelah event `OrderPaid` |
| Retry | 3x backoff (1, 5, 15 menit) |
| Logging | Simpan request/response ringkas ke `whatsapp_logs` |
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

Pesanan akan segera kami proses. Terima kasih sudah berbelanja!
```

**Ke admin (setelah pembayaran sukses):**
```
Pesanan baru LUNAS {{order_number}}
Pembeli: {{nama}} ({{phone}})
Jumlah item: {{jumlah_item}}
Total: Rp{{total}}

Buka dashboard: {{url_dashboard}}
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
| R-02 | QR absensi difoto dan dipakai orang lain | Data kehadiran tidak akurat | Token berumur pendek, wajib login, catat IP; fase 2 tambah selfie/GPS |
| R-03 | Webhook Midtrans tidak diterima | Pesanan tetap pending | Sediakan halaman cek status manual + sinkronisasi status via API Midtrans |
| R-04 | Stok tidak akurat karena penjualan offline | Overselling | Admin wajib update stok; validasi stok saat checkout; penanda stok menipis |
| R-05 | Personalisasi salah tulis oleh pembeli | Komplain/retur | Tampilkan preview + konfirmasi sebelum bayar; teks tercatat di WA dan detail pesanan |
| R-06 | Ongkir flat tidak sesuai ongkos kirim riil | Kerugian biaya kirim | Admin menetapkan tarif konservatif; integrasi kurir di fase 2 |
| R-07 | Beban grafik memperlambat dashboard | UX buruk | Agregasi server + cache hasil query 5 menit + indeks database |

### 12.1 Asumsi Teknis
- Server mendukung PHP 8.2+, MySQL 8, queue worker (database/redis), dan HTTPS.
- Storage gambar menggunakan local disk atau S3-compatible (dapat dikonfigurasi).
- Zona waktu aplikasi `Asia/Jakarta`.

---

## 13. Roadmap

### Fase 1 — MVP (sesuai dokumen ini)
Katalog, personalisasi, akun, keranjang, checkout, Midtrans, notifikasi WA setelah pembayaran, dashboard admin, grafik penjualan, absensi QR, role admin/karyawan.

### Fase 2 — Peningkatan
- Notifikasi WA untuk setiap perubahan status (diproses, dikirim, selesai) + template yang dapat diedit admin.
- Ulasan produk dan rating.
- Wishlist.
- Integrasi ongkir otomatis (RajaOngkir).
- Absensi dengan selfie dan validasi GPS.
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
| Q-05 | Jam kerja dan hari kerja karyawan (default Senin–Sabtu, 08:00–17:00)? | Pemilik toko | Sebelum modul absensi |
| Q-06 | Apakah karyawan juga perlu akses memproses pesanan? | Pemilik toko | Sebelum development |
| Q-07 | Metode pengiriman yang tersedia (JNE, J&T, SiCepat, kurir instan)? | Pemilik toko | Fase 2 |
| Q-08 | Apakah diperlukan invoice PDF? | Pemilik toko | Fase 2 |

---

## Lampiran A — Daftar Prioritas (MoSCoW)

| Prioritas | Fitur |
|---|---|
| Must | Katalog, detail produk, personalisasi, akun, keranjang, checkout, Midtrans, WA setelah pembayaran, dashboard admin, grafik penjualan, absensi QR, role |
| Should | Kupon, fallback email, tes kirim WA, filter/urut katalog, manajemen pengguna, pengaturan toko |
| Could | Preview personalisasi real-time, export laporan lanjutan, invoice PDF |
| Won't (MVP) | Marketplace, kurir otomatis, mobile app, ulasan, wishlist, selfie/GPS absensi |

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
