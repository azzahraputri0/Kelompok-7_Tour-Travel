<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');
        $hariIni = Carbon::today();

        $akun = [
            ['USR-2026-001', 'Rina Wulandari', 'staff@traveltour.test', '081234560001', 'Staff', null, null],
            ['USR-2026-002', 'Hendra Gunawan', 'manager@traveltour.test', '081234560002', 'Manager', null, null],
            ['USR-2026-003', 'Budi Santoso', 'driver@traveltour.test', '081234560003', 'Driver', null, null],
            ['USR-2026-004', 'Agus Pratama', 'driver2@traveltour.test', '081234560004', 'Driver', null, null],
            ['USR-2026-005', 'Siti Rahmawati', 'siti.rahmawati@mail.test', '081311220005', 'Customer', '3171014501900005', 'uploads/identitas/ktp-contoh.svg'],
            ['USR-2026-006', 'Dewi Lestari', 'dewi.lestari@mail.test', '081311220006', 'Customer', '3273026702920006', 'uploads/identitas/ktp-contoh.svg'],
            ['USR-2026-007', 'Andi Saputra', 'andi.saputra@mail.test', '081311220007', 'Customer', '3578031103880007', null],
            ['USR-2026-008', 'Maya Anggraini', 'maya.anggraini@mail.test', '081311220008', 'Customer', '5171044408950008', 'uploads/identitas/ktp-contoh.svg'],
            ['USR-2026-009', 'Rizky Ramadhan', 'rizky.ramadhan@mail.test', '081311220009', 'Customer', null, null],
            ['USR-2026-010', 'Putri Ayu Lestari', 'putri.ayu@mail.test', '081311220010', 'Customer', '3374055209970010', 'uploads/identitas/ktp-contoh.svg'],
            ['USR-2026-011', 'Fajar Nugroho', 'fajar.nugroho@mail.test', '081311220011', 'Customer', null, null],
            ['USR-2026-012', 'Nadia Kusuma', 'nadia.kusuma@mail.test', '081311220012', 'Customer', '3201066106990012', 'uploads/identitas/ktp-contoh.svg'],
        ];
        foreach ($akun as [$id, $nama, $email, $hp, $role, $nik, $dokumen]) {
            DB::table('UMUM')->insert([
                'id_pengguna' => $id, 'nama_lengkap' => $nama, 'email' => $email, 'nomor_telepon' => $hp,
                'password' => $password, 'role' => $role, 'nomor_identitas' => $nik, 'dokumen_identitas' => $dokumen,
            ]);
        }

        $paket = [
            ['PKT-BALI-01', 'Bali Explore 4D3N', 'Bali', '4 Hari 3 Malam', 2500000, 'Pantai, Ubud, dan Tanah Lot', 'paket-bali.jpg'],
            ['PKT-BRMO-01', 'Bromo Sunrise 2D1N', 'Malang', '2 Hari 1 Malam', 1250000, 'Sunrise Penanjakan dan kawah Bromo', 'paket-bromo.jpg'],
            ['PKT-LMBK-01', 'Lombok Gili Trip 3D2N', 'Lombok', '3 Hari 2 Malam', 2900000, 'Gili Trawangan dan Pantai Pink', 'paket-lombok.jpg'],
            ['PKT-LBJ-01', 'Labuan Bajo Sailing 3D2N', 'Labuan Bajo', '3 Hari 2 Malam', 3500000, 'Pulau Komodo dan Padar', 'paket-labuan.jpg'],
            ['PKT-TOBA-01', 'Danau Toba Heritage 3D2N', 'Sumatera Utara', '3 Hari 2 Malam', 2100000, 'Samosir dan Desa Tomok', 'paket-toba.jpg'],
        ];
        foreach ($paket as $i => [$id, $nama, $tujuan, $durasi, $harga, $deskripsi, $foto]) {
            DB::table('PAKET_TRAVEL')->insert([
                'id_paket' => $id, 'nama_paket' => $nama, 'tujuan' => $tujuan, 'durasi' => $durasi,
                'harga' => $harga, 'kuota' => 20, 'jadwal_keberangkatan' => $hariIni->copy()->addDays(10 + $i * 7)->toDateString(),
                'deskripsi' => $deskripsi, 'foto_paket' => 'assets/img/'.$foto,
            ]);
        }

        $kendaraan = [
            ['KND-001', 'B 7123 TXA', 'Minibus', 'Toyota Hiace', 14, 2023],
            ['KND-002', 'B 7456 TXB', 'Bus Medium', 'Isuzu Elf', 19, 2022],
            ['KND-003', 'B 1890 TXC', 'MPV', 'Toyota Innova', 7, 2024],
        ];
        foreach ($kendaraan as [$id, $plat, $jenis, $merek, $kapasitas, $tahun]) {
            DB::table('KENDARAAN')->insert([
                'id_kendaraan' => $id, 'plat_nomor' => $plat, 'jenis_kendaraan' => $jenis, 'merek' => $merek,
                'kapasitas' => $kapasitas, 'tahun_produksi' => $tahun, 'status' => 'Tersedia',
            ]);
        }

        DB::table('DRIVER')->insert([
            ['id_driver' => 'DRV-001', 'nama_lengkap' => 'Budi Santoso', 'nomor_telepon' => '081234560003', 'nomor_sim' => '920312345601', 'alamat' => 'Jl. Kenanga No. 12, Malang', 'status' => 'Bertugas', 'FK_id_pengguna' => 'USR-2026-003'],
            ['id_driver' => 'DRV-002', 'nama_lengkap' => 'Agus Pratama', 'nomor_telepon' => '081234560004', 'nomor_sim' => '920312345602', 'alamat' => 'Jl. Melati No. 7, Surabaya', 'status' => 'Tersedia', 'FK_id_pengguna' => 'USR-2026-004'],
        ]);

        $pelanggan = array_column(array_filter($akun, fn ($a) => $a[4] === 'Customer'), 0);
        $hargaPaket = array_column($paket, 4, 0);
        $idPaket = array_keys($hargaPaket);
        $peserta = [2, 4, 3, 5, 2, 6, 1, 3];
        $lunas = [];

        for ($i = 1; $i <= 20; $i++) {
            $tanggalBayar = $hariIni->copy()->subDays((20 - $i) * 9);
            $paketDipilih = $idPaket[($i - 1) % count($idPaket)];
            $jumlah = $peserta[$i % count($peserta)];
            $total = $hargaPaket[$paketDipilih] * $jumlah;
            $statusBayar = match (true) {
                $i % 7 === 0 => 'Ditolak',
                $i % 5 === 0 => 'Proses Verifikasi',
                default => 'Lunas',
            };
            $idPemesanan = sprintf('PSN-%s-%03d', $tanggalBayar->format('Ym'), $i);

            DB::table('PEMESANAN')->insert([
                'id_pemesanan' => $idPemesanan,
                'kode_pemesanan' => sprintf('TRV-%s%02d', $tanggalBayar->format('ymd'), $i),
                'FK_id_pengguna' => $pelanggan[($i - 1) % count($pelanggan)],
                'FK_id_paket' => $paketDipilih,
                'jadwal_keberangkatan' => $tanggalBayar->copy()->addDays(21)->toDateString(),
                'jumlah_peserta' => $jumlah,
                'total_pembayaran' => $total,
                'status' => ['Lunas' => 'Terkonfirmasi', 'Proses Verifikasi' => 'Menunggu Verifikasi', 'Ditolak' => 'Dibatalkan'][$statusBayar],
                'status_kuota' => $statusBayar === 'Ditolak' ? 'Kuota Dilepas' : 'Kuota Terkunci',
                'batas_waktu_pembayaran' => $tanggalBayar->toDateString(),
            ]);

            DB::table('PEMBAYARAN')->insert([
                'id_pembayaran' => sprintf('PYM-%s-%03d', $tanggalBayar->format('Ym'), $i),
                'nomor_invoice' => sprintf('INV/%s/%03d', $tanggalBayar->format('Y/m'), $i),
                'id_pemesanan' => $idPemesanan,
                'nominal' => $total,
                'bukti_pembayaran' => sprintf('bukti_transfer_%03d.jpg', $i),
                'tanggal_bayar' => $tanggalBayar->toDateString(),
                'status' => $statusBayar,
            ]);

            if ($statusBayar === 'Lunas') {
                $lunas[] = [$idPemesanan, $tanggalBayar->copy()->addDays(21)];
            }
        }

        // Jadwal hanya untuk DRV-001 supaya DRV-002 bisa dipakai menguji pesan "Jadwal Tour tidak tersedia".
        foreach (array_slice($lunas, -8) as $n => [$idPemesanan, $berangkat]) {
            DB::table('JADWAL_PERJALANAN')->insert([
                'id_jadwal' => sprintf('JDW-2026-%03d', $n + 1),
                'id_pemesanan' => $idPemesanan,
                'id_kendaraan' => $kendaraan[$n % count($kendaraan)][0],
                'id_driver' => 'DRV-001',
                'tanggal_berangkat' => $berangkat->toDateString(),
                'status' => $berangkat->lt($hariIni) ? 'Selesai' : 'Terjadwal',
            ]);
        }
    }
}
