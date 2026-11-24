# ERP API — Backend Sistem Distribusi & Penjualan

![PHP](https://img.shields.io/badge/PHP-7.3%20%7C%208.x-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)
![Passport](https://img.shields.io/badge/Auth-Laravel%20Passport-blue)
![WebSockets](https://img.shields.io/badge/Realtime-WebSockets-green)

RESTful API berbasis **Laravel 8** yang menjadi backend sebuah aplikasi **ERP (Enterprise Resource Planning)** untuk usaha distribusi/perdagangan. API ini menangani seluruh siklus bisnis: mulai dari pesanan pelanggan, pembelian ke supplier, penjualan, retur, manajemen stok dan HPP, hingga hutang-piutang dan pelaporan keuangan.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Alur Bisnis](#alur-bisnis)
- [Tech Stack](#tech-stack)
- [Arsitektur & Struktur Folder](#arsitektur--struktur-folder)
- [Keamanan & Hak Akses](#keamanan--hak-akses)
- [Instalasi](#instalasi)
- [Contoh Endpoint](#contoh-endpoint)
- [Pengujian](#pengujian)
- [Pengembang](#pengembang)

---

## Fitur Utama

### Master Data
| Modul | Keterangan |
|---|---|
| Customer & Group Customer | Data pelanggan beserta pengelompokannya |
| Supplier | Data pemasok |
| Product, Group Product, Satuan | Katalog produk, kategori, dan satuan |
| Karyawan & Armada | Data karyawan dan kendaraan pengiriman |
| Bank & Alat Bayar | Rekening dan metode pembayaran |
| Perkiraan (COA) | Chart of Accounts untuk pencatatan keuangan |
| Owner, Parameter, Hari Libur | Konfigurasi umum sistem |

### Transaksi
- **Pesanan** — pencatatan pesanan pelanggan (header–detail).
- **Pesanan Final & Cek Pesanan** — rekap pesanan final yang menjadi dasar pembelian dan penjualan, termasuk cetak *surat pengantar barang*.
- **Pembelian** — pembelian barang ke supplier, termasuk cek stok produk dan transaksi belanja.
- **Penjualan** — penjualan ke pelanggan, cetak *invoice*, dan laporan profit (ringkas maupun detail).
- **Faktur Pembelian & Faktur Penjualan**.
- **Retur Beli & Retur Jual** — dengan validasi qty maksimal yang boleh diretur.
- **Penyesuaian Stok** — koreksi stok fisik.
- **Transaksi Armada & Transaksi Belanja** — biaya operasional kendaraan dan belanja harian.

### Keuangan
- **Hutang & Pelunasan Hutang** ke supplier.
- **Piutang & Pelunasan Piutang** dari pelanggan.

### Persediaan
- **Kartu Stok** — riwayat keluar-masuk barang per produk.
- **HPP (Harga Pokok Penjualan)** — perhitungan HPP dari pembelian, penjualan, dan retur.

### Sistem & Administrasi
- Manajemen **User, Role, Menu, ACL/ACOS** (hak akses per role maupun per user).
- **Log Trail** — audit trail setiap perubahan data (header & detail).
- **Dashboard**, **Error log**, ubah & lupa password (reset via email).
- **Running Number** — penomoran dokumen transaksi otomatis per grup/sub-grup dan periode.
- **Export Excel & Report** di hampir setiap modul (PhpSpreadsheet).
- **Notifikasi real-time** untuk transaksi pembelian & penjualan melalui WebSockets.

---

## Alur Bisnis

```
 Pesanan ──► Pesanan Final ──┬──► Pembelian ──► Hutang ──► Pelunasan Hutang
                             │        │
                             │        └──► Retur Beli
                             │
                             └──► Penjualan ──► Piutang ──► Pelunasan Piutang
                                      │
                                      ├──► Invoice / Surat Pengantar Barang
                                      └──► Retur Jual

 Pembelian / Penjualan / Retur / Penyesuaian Stok ──► Kartu Stok ──► HPP ──► Laporan Profit
```

---

## Tech Stack

| Kategori | Teknologi |
|---|---|
| Bahasa | PHP 7.3+ / 8.x |
| Framework | Laravel 8 |
| Database | MySQL / MariaDB |
| Autentikasi | Laravel Passport (OAuth2), Laravel Sanctum |
| Real-time | `beyondcode/laravel-websockets`, Pusher protocol |
| Export / Laporan | `phpoffice/phpspreadsheet` |
| Lainnya | Intervention Image, Guzzle HTTP, Doctrine DBAL, Laravel CORS |
| Testing | PHPUnit, Paratest, Mockery, Faker |

---

## Arsitektur & Struktur Folder

```
erp-api/
├── app/
│   ├── Console/              # Kernel artisan & scheduler
│   ├── Events/               # Event broadcast (notifikasi pembelian/penjualan, progres export)
│   ├── Exceptions/           # Global exception handler
│   ├── Helpers/              # Fungsi helper global (autoload via composer)
│   ├── Http/
│   │   ├── Controllers/Api/  # ±60 controller REST, satu per modul
│   │   ├── Middleware/       # Authorized (cek ACL), HandleTokenExpiration, dll.
│   │   └── Requests/         # Form Request validation (Store/Update/Destroy)
│   ├── Mail/                 # Email reset password
│   ├── Models/               # Eloquent model + query builder grid (filter, sort, paging)
│   ├── Providers/            # Service provider (Auth, Broadcast, Route, dll.)
│   ├── Rules/                # Custom validation rules
│   ├── Services/             # RunningNumberService (penomoran dokumen)
│   └── Traits/               # RestrictDeletion (mencegah hapus data yang sudah dipakai)
├── config/                   # Konfigurasi Laravel + integrasi API eksternal
├── cronjob/                  # Script terjadwal (dipanggil via Task Scheduler / cron)
├── database/
│   ├── migrations/           # 60+ migration skema tabel
│   ├── factories/
│   └── seeders/              # Seeder master data, menu, role & ACL
├── routes/
│   ├── api.php               # Seluruh endpoint REST API
│   └── channels.php          # Otorisasi channel broadcasting
├── tests/Feature/Api/        # Feature test endpoint API
└── public/                   # Entry point aplikasi
```

### Pola yang Digunakan
- **Header–Detail pattern** untuk dokumen transaksi (mis. `PenjualanHeader` + `PenjualanDetail`), sehingga satu dokumen dapat memiliki banyak baris item.
- **Form Request Validation** terpisah untuk create, update, dan delete.
- **Grid endpoint standar** di setiap modul: pencarian, filter per kolom, sorting, dan pagination di sisi server.
- **Endpoint pendukung** yang konsisten: `/default` (nilai awal form), `/field_length` (panjang field untuk validasi di frontend), `/combo`, `/export`, `/report`.
- **Database transaction** pada proses simpan transaksi untuk menjaga konsistensi data.
- **Audit trail** otomatis ke tabel `logtrail` untuk setiap perubahan.

---

## Keamanan & Hak Akses

1. **Autentikasi OAuth2** menggunakan Laravel Passport — client mendapatkan token melalui `POST /api/token`.
2. **Middleware `auth:api`** melindungi seluruh endpoint transaksi.
3. **Middleware `Authorized`** memeriksa izin berdasarkan kombinasi *class* (modul) dan *method* (aksi) terhadap tabel ACL milik role maupun user, lalu mengembalikan `403` bila tidak berhak.
4. **HandleTokenExpiration** untuk menangani token yang kedaluwarsa.
5. **RestrictDeletion** mencegah penghapusan master data yang masih direferensikan oleh transaksi.

---

## Instalasi

### Prasyarat
- PHP 7.3+ dengan ekstensi `pdo_mysql`, `gd`, `zip`, `mbstring`
- Composer
- MySQL 5.7+ / MariaDB 10.3+

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/thinkabowtsya/erp-laravelapi.git
cd erp-laravelapi

# 2. Install dependency
composer install

# 3. Salin file environment lalu sesuaikan koneksi database
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi & seeder
php artisan migrate --seed

# 5. Generate key OAuth Passport
php artisan passport:install

# 6. Jalankan server
php artisan serve
```

API dapat diakses di `http://localhost:8000/api`.

### (Opsional) Server WebSockets untuk notifikasi real-time

Atur `BROADCAST_DRIVER=pusher` dan kredensial `PUSHER_*` di `.env`, lalu jalankan:

```bash
php artisan websockets:serve
```

---

## Contoh Endpoint

| Method | Endpoint | Keterangan |
|---|---|---|
| `POST` | `/api/token` | Login & ambil access token |
| `GET` | `/api/penjualanheader` | Daftar penjualan (grid: filter, sort, paging) |
| `POST` | `/api/penjualanheader` | Simpan penjualan baru (header + detail) |
| `GET` | `/api/penjualanheader/{id}/invoice` | Data cetak invoice |
| `GET` | `/api/penjualanheader/reportprofit` | Laporan profit penjualan |
| `GET` | `/api/pembelianheader/cekstokproduct` | Cek stok produk sebelum pembelian |
| `GET` | `/api/pesananfinalheader/{id}/suratpengantarbarang` | Data surat pengantar barang |
| `GET` | `/api/pelunasanpiutangheader/getpiutang` | Daftar piutang yang belum lunas |
| `GET` | `/api/customer/export` | Export data customer ke Excel |
| `GET` | `/api/logtrail/header` | Riwayat audit trail |

Semua endpoint selain `/api/token` membutuhkan header:

```
Authorization: Bearer <access_token>
Accept: application/json
```

---

## Pengujian

```bash
php artisan test
```

Feature test berada di `tests/Feature/Api` (autentikasi, user, role, parameter, error).

---

## Pengembang

**Syarifah** — Backend Developer

- GitHub: [@thinkabowtsya](https://github.com/thinkabowtsya)

---

> Repository ini dipublikasikan sebagai portofolio. Data, kredensial, dan dump database produksi tidak disertakan.
