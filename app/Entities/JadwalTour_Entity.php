<?php

namespace App\Entities;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// CD-26: entity Jadwal Tour (JADWAL_PERJALANAN milik driver yang login).
class JadwalTour_Entity
{
    public function idDriver(string $idPengguna): ?string
    {
        return DB::table('DRIVER')->where('FK_id_pengguna', $idPengguna)->value('id_driver');
    }

    private function jadwal(): Builder
    {
        return DB::table('JADWAL_PERJALANAN as j')
            ->join('PEMESANAN as p', 'p.id_pemesanan', '=', 'j.id_pemesanan')
            ->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket');
    }

    // PC-40
    public function getAll_data_jadwalTour(?string $idDriver): Collection
    {
        if (! $idDriver) {
            return collect();
        }

        return $this->jadwal()
            ->where('j.id_driver', $idDriver)
            ->orderBy('j.tanggal_berangkat')
            ->get(['j.id_jadwal', 'j.tanggal_berangkat', 'j.status', 'p.kode_pemesanan', 'k.nama_paket', 'k.tujuan']);
    }

    // PC-41: hanya mengembalikan jadwal bila milik driver tersebut.
    public function get_data_detail_jadwalTour(string $idJadwal, ?string $idDriver): ?object
    {
        if (! $idDriver) {
            return null;
        }

        return $this->jadwal()
            ->join('KENDARAAN as v', 'v.id_kendaraan', '=', 'j.id_kendaraan')
            ->join('UMUM as u', 'u.id_pengguna', '=', 'p.FK_id_pengguna')
            ->where('j.id_jadwal', $idJadwal)
            ->where('j.id_driver', $idDriver)
            ->first([
                'j.id_jadwal', 'j.tanggal_berangkat', 'j.status',
                'p.kode_pemesanan', 'p.jumlah_peserta',
                'k.nama_paket', 'k.tujuan', 'k.durasi', 'k.deskripsi', 'k.foto_paket',
                'v.plat_nomor', 'v.merek', 'v.jenis_kendaraan', 'v.kapasitas',
                'u.nama_lengkap as nama_pemesan', 'u.nomor_telepon as telepon_pemesan',
            ]);
    }
}
