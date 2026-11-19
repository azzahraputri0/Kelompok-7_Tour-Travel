# Travel Tour — Sistem Lengkap

Aplikasi Laravel 11 / PHP 8.2 untuk 18 use case: pelanggan, admin, staff, driver, dan manager. Modul laporan, data pelanggan, dan jadwal tour yang sudah ada tetap digunakan. Semua perubahan database tambahan terpisah dalam migration baru; jangan mengganti database kelompok dengan data demo.

## Persyaratan
- PHP 8.2+ dengan PDO MySQL (atau PDO SQLite), mbstring, openssl, DOM/XML, GD, ZIP, dan fileinfo.
- MySQL 8 untuk XAMPP/Laragon atau SQLite untuk demonstrasi cepat.
- Composer 2 bila folder `vendor` belum tersedia. Paket ZIP runnable menyertakan `vendor`; repository tidak.
- Tidak memerlukan Node.js, npm, atau server eksternal untuk antarmuka.

## Menjalankan dengan MySQL
1. Ekstrak paket. Buat database kosong `travel_tour` lewat phpMyAdmin.
2. Salin `.env.example` menjadi `.env`. Isi host, nama database, username dan password MySQL sendiri.
3. Di folder aplikasi, jalankan:
   ```sh
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan db:seed --class=DemoAdminSeeder
   php artisan serve
   ```
   `composer install` boleh dilewati bila `vendor` sudah disertakan.
4. Buka http://127.0.0.1:8000.
5. Alternatif database baru: import `database/travel_tour.sql`, lalu jalankan `php artisan migrate` tanpa `--seed`. Jangan import dump ke database kelompok yang berisi data penting. Dump ditujukan hanya untuk database kosong.

## Demonstrasi cepat dengan SQLite
1. Salin `.env.example` menjadi `.env`.
2. Buat file kosong `database/demo.sqlite`.
3. Atur `DB_CONNECTION=sqlite` dan `DB_DATABASE` ke path absolut file tersebut. Pengaturan `DB_HOST`, `DB_USERNAME`, dan `DB_PASSWORD` tidak digunakan SQLite.
4. Jalankan `php artisan key:generate`, `php artisan migrate --seed`, `php artisan db:seed --class=DemoAdminSeeder`, lalu `php artisan serve`.

## Melanjutkan database kelompok yang sudah ada
- Backup database lebih dulu.
- Sesuaikan `.env`, lalu jalankan `php artisan migrate` saja.
- **Jangan** jalankan `migrate:fresh`, import SQL demo, atau `--seed` pada database kelompok yang sudah berisi data.
- Migration `2026_11_19_000003_complete_travel` menambah fasilitas/biaya admin paket, data kontak dan penumpang pemesanan, alasan penolakan, tautan grup, serta tenggat pembayaran. Nama tabel dan foreign key asli tetap digunakan.

## Akun demonstrasi lokal
Password semua akun demonstrasi: `password123`.
- Admin: `admin@traveltour.test` — dibuat hanya melalui `DemoAdminSeeder` secara eksplisit.
- Staff: `staff@traveltour.test`.
- Manager: `manager@traveltour.test`.
- Driver: `driver@traveltour.test`.
- Driver kedua: `driver2@traveltour.test`.
- Pelanggan: `siti.rahmawati@mail.test`.

`DemoAdminSeeder` menolak environment production dan dapat dijalankan ulang tanpa menggandakan akun. Ganti seluruh kredensial demo sebelum penggunaan sungguhan. Pendaftaran publik selalu membuat role Customer, bukan Admin atau Staff.

## Alur pesan dan bayar
1. Pelanggan memilih paket dan jadwal yang tersedia.
2. Isi jumlah peserta, kontak pemesan, dan data penumpang. Total dihitung di server dari harga paket dan biaya admin.
3. Setelah pesanan dibuat, kuota ditahan dengan tenggat pembayaran 24 jam. Status awal: **Belum Terbayar**.
4. Buka pembayaran, periksa rincian transfer, lalu unggah bukti JPG/PNG/PDF, maksimal 4 MB.
5. Status berubah menjadi **Menunggu Verifikasi**. Ini belum berarti pembayaran berhasil.
6. Staff memeriksa data pemesanan dan bukti transfer. Persetujuan menjadikan pembayaran **Lunas** dan pemesanan **Terkonfirmasi** dalam satu proses; tidak ada konfirmasi ganda yang wajib dilakukan pelanggan.
7. Jika bukti ditolak, alasan ditampilkan dan pelanggan dapat mengirim ulang sebelum tenggat. Penolakan pesanan oleh staff membutuhkan alasan.
8. Staff menugaskan driver dan kendaraan pada pesanan terkonfirmasi. Sistem memeriksa kapasitas dan bentrok jadwal. Driver melihat jadwal miliknya sendiri.

Tautan grup WhatsApp opsional dan tidak menjadi syarat pembayaran sah. Sistem tidak mengirim pesan WhatsApp otomatis. Tidak ada QR pembayaran aktif, payment gateway, atau e-ticket tambahan di luar lingkup use case.

### Rekening tujuan
Isi hanya rekening resmi yang sudah dikonfirmasi pada `.env`:
```dotenv
TRAVEL_BANK_NAME=
TRAVEL_BANK_ACCOUNT=
TRAVEL_BANK_HOLDER=
```
Jika kosong, halaman menampilkan peringatan **DEMO — jangan melakukan transfer**. Tidak ada nomor rekening karangan. Setelah mengubah konfigurasi, jalankan `php artisan config:clear`.

### Tenggat pesanan
Pesanan belum dibayar yang melewati tenggat akan kedaluwarsa dan kuotanya tersedia kembali. Pemeriksaan juga dijalankan ketika daftar/detail pesanan diakses. Untuk aplikasi yang berjalan terus-menerus, aktifkan Laravel scheduler (`php artisan schedule:work` untuk lokal, atau cron `schedule:run` di server). Bukti yang sedang ditinjau tidak dianggap sebagai belum dibayar.

## Fitur per role dan pemetaan use case
- UC-01: login/logout dan pendaftaran Customer.
- UC-02 / UC-03: katalog dan detail paket.
- UC-04 / UC-05 / UC-06: CRUD paket travel, kendaraan, dan driver oleh Admin.
- UC-07: penugasan kendaraan dan driver / jadwal perjalanan.
- UC-08 / UC-09: pemeriksaan pemesanan dan verifikasi pembayaran oleh Staff.
- UC-10 / UC-11 / UC-12: laporan transaksi, kirim laporan, dan data pelanggan.
- UC-13 / UC-14 / UC-15: pemesanan, bukti pembayaran, dan status pesanan Customer.
- UC-16: jadwal dan detail tour Driver.
- UC-17 / UC-18: peninjauan laporan dan ekspor PDF/Excel Manager.

Nomor UC berbeda dengan nomor sequence diagram; proyek mencakup 28 sequence diagram. Modul AP tetap memakai `Laporan_Kontrol` / `Laporan_Entity`, `Pelanggan_Kontrol` / `Pelanggan_Entity`, dan `JadwalTour_Kontrol` / `JadwalTour_Entity`.

## Laporan dan email
Saat pertama dibuka, laporan menampilkan hari transaksi paling terakhir (hari ini bila belum ada transaksi). Filter manual tetap tersedia. Daftar transaksi memiliki area gulir sendiri; ringkasan, total, dan ekspor berada di luar area tersebut. Pilih jenis periode, tanggal awal, dan tanggal akhir, lalu tekan **Tampilkan**. Manager dapat mengunduh PDF dan Excel. Default `MAIL_MAILER=log`: kirim laporan dicatat di `storage/logs/laravel.log`, **bukan** email yang benar-benar terkirim. Untuk email sungguhan, konfigurasi SMTP milik pengelola pada `.env`.

## Penyimpanan dan privasi
Bukti pembayaran dan unggahan identitas disimpan di private storage, bukan URL publik. Pemilik berkas dan role pemeriksa divalidasi sebelum file diberikan. Jangan mengubah storage private menjadi public atau membagikan `.env`, log aplikasi, database nyata, atau dokumen identitas pelanggan.

## Pengujian
```sh
php artisan test
php artisan route:list --except-vendor
```
Tes otomatis default memakai SQLite in-memory dari `phpunit.xml`, terpisah dari database aplikasi. PHP 8.2 + MySQL diverifikasi terpisah. Bukti hasil tes dan tangkapan layar dikirim bersama paket, bukan klaim bahwa konfigurasi SMTP/rekening produksi sudah aktif.

## Revisi tampilan dan migrasi foto/metode
Jalankan `php artisan migrate` setelah pembaruan maupun import SQL demo, lalu `php artisan storage:link`. Migration `000004_add_vehicle_photo` dan `000005_add_driver_photo_and_payment_method` menambah foto kendaraan/driver dan metode pembayaran tanpa mengganti transaksi historis. Foto driver/kendaraan bersifat publik; jangan unggah identitas pribadi ke kolom foto ini.

Seeder baru memakai label QRIS untuk data demonstrasi laporan. Data historis tetap Transfer Bank; PDF/Excel mengikuti metode tersimpan. Tidak ada QR buatan, pemindai, atau payment gateway. Alur pembayaran baru tetap transfer manual sesuai konfigurasi rekening yang ada; **QRIS manual belum diaktifkan**. Label QRIS pada data demo bukan kesiapan menerima pembayaran QRIS. Untuk mengaktifkannya dibutuhkan aset QRIS resmi pengelola dan konfigurasi/alur terverifikasi tersendiri, di luar revisi laporan ini.
