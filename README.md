# Travel Tour — Bagian [- AP]

Aplikasi Laravel 11 untuk UC-10, UC-11, UC-12 (Staff), UC-16 (Driver), UC-17, UC-18 (Manager).

## Syarat
- PHP 8.2 atau lebih baru, ekstensi: pdo_mysql, mbstring, openssl, dom, gd, zip, fileinfo
- MySQL 5.7 / 8 (XAMPP / Laragon juga bisa)
- Composer 2 (folder `vendor/` tidak ikut di repo, jalankan `composer install` dulu).

## Cara menjalankan
1. Buat database kosong bernama `travel_tour`.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan `DB_USERNAME` dan `DB_PASSWORD`.
3. Jalankan:
   ```
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   ```
   Atau, tanpa migrate: import `database/travel_tour.sql` lewat phpMyAdmin, lalu `php artisan key:generate` dan `php artisan serve`.
4. Buka http://127.0.0.1:8000

## Akun demo (password: `password123`)
| Role | Email |
|---|---|
| Staff | staff@traveltour.test |
| Manager | manager@traveltour.test |
| Driver | driver@traveltour.test |
| Driver (tanpa jadwal) | driver2@traveltour.test |

Akun Customer tidak bisa masuk ke halaman ini.

## Peta UC → Class Diagram → Pseudocode → Kode
| UC | CD | PC | Kode |
|---|---|---|---|
| UC-10 Melihat Laporan Transaksi | CD-20 | PC-31, PC-32 | `Laporan_Kontrol::getAll_transaksi`, `get_transaksi`, `cek_transaksi` → `Laporan_Entity::getAll_data_transaksi`, `get_data_transaksi` → `staff/j1_halaman_laporan` |
| UC-11 Mengirim Laporan Transaksi | CD-20 | PC-33, PC-34 | `Laporan_Kontrol::kirim_laporan_transaksi` → `Laporan_Entity::kirim_data_laporan_transaksi` |
| UC-12 Memeriksa Data Pelanggan | CD-21, CD-22 | PC-35, PC-36, PC-37 | `Pelanggan_Kontrol::getAll_pelanggan`, `cari_pelanggan`, `cek_data_pelanggan`, `get_detail_pelanggan` → `Pelanggan_Entity` → `pelanggan/k1_halaman_pelanggan`, `k1_halaman_detail_pelanggan` |
| UC-16 Melihat Jadwal Tour | CD-26 | PC-40, PC-41 | `JadwalTour_Kontrol::getAll_jadwalTour`, `cek_jadwalTour`, `get_detail_jadwalTour` → `JadwalTour_Entity` → `driver/p1_halaman_jadwal_tour`, `p2_halaman_detail_jadwal_tour` |
| UC-17 Meninjau Laporan | CD-27 | PC-42, PC-43 | `Laporan_Kontrol::getAll_transaksi`, `get_transaksi` → `manager/q1_halaman_laporan` |
| UC-18 Mengekspor Laporan | CD-28 | PC-44, PC-45 | `Laporan_Kontrol::ekspor_laporan`, `proses_ekspor_file` (PDF / Excel) |

Setiap method diberi komentar nomor PC dan baris `CALL` dari pseudocode.

## Route
- `GET /login`, `POST /login`, `POST /logout`
- Staff: `/staff/dashboard`, `/staff/laporan`, `POST /staff/laporan/kirim`
- Staff & Manager: `/pelanggan`, `/pelanggan/cari?keyword=`, `/pelanggan/{id}`
- Driver: `/driver/dashboard`, `/driver/jadwal-tour`, `/driver/jadwal-tour/{id}`
- Manager: `/manager/dashboard`, `/manager/laporan`, `/manager/laporan/ekspor?format_file=pdf|xlsx`

## Database
Nama tabel dan kolom mengikuti DDL tim (UMUM, PAKET_TRAVEL, KENDARAAN, DRIVER, PEMESANAN, PEMBAYARAN) dan PDM (JADWAL_PERJALANAN). Penyesuaian dipisah di `database/migrations/2026_11_09_000002_tambahan_bagian_ap.php`:
- `UMUM.password` jadi VARCHAR(255), karena hash bcrypt tidak muat di VARCHAR(50).
- `UMUM.nomor_identitas` dan `UMUM.dokumen_identitas` (nullable) untuk detail pelanggan UC-12.
- `DRIVER.FK_id_pengguna` (sesuai PDM) untuk menghubungkan akun login driver dengan data driver.
- FK di `PEMESANAN` mengikuti PDM (`FK_id_pengguna` → UMUM, `FK_id_paket` → PAKET_TRAVEL), karena di DDL FK-nya menunjuk ke tabelnya sendiri.

## Asumsi
- Pelanggan = data UMUM dengan role `Customer`. Status "Terverifikasi" bila nomor identitas dan dokumen identitas sudah terisi.
- Transaksi = data PEMBAYARAN. Ringkasan (jumlah lunas, peserta, total) hanya menghitung pembayaran berstatus `Lunas`.
- Kolom metode pembayaran tidak ada di DDL, jadi ditampilkan "Transfer Bank".
- Kirim laporan mengirim email (dengan lampiran PDF) ke semua akun Manager. Bawaannya `MAIL_MAILER=log`, jadi email tertulis di `storage/logs/laravel.log`. Untuk kirim sungguhan, isi pengaturan SMTP di `.env`.

## Test
```
php artisan test
```
