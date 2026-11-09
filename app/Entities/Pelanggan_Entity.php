<?php

namespace App\Entities;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// CD-21, CD-22: entity Pelanggan = akun UMUM dengan role Customer.
class Pelanggan_Entity
{
    private function pelanggan(): Builder
    {
        return DB::table('UMUM')
            ->where('role', 'Customer')
            ->select(['id_pengguna', 'nama_lengkap', 'email', 'nomor_telepon', 'nomor_identitas', 'dokumen_identitas'])
            ->orderBy('nama_lengkap');
    }

    private function denganStatus(Collection $daftar): Collection
    {
        return $daftar->map(function ($p) {
            $p->status_data = filled($p->nomor_identitas) && filled($p->dokumen_identitas) ? 'Terverifikasi' : 'Belum Lengkap';

            return $p;
        });
    }

    // PC-35
    public function getAll_data_pelanggan(): Collection
    {
        return $this->denganStatus($this->pelanggan()->get());
    }

    // PC-36: cari berdasarkan nama atau nomor HP.
    public function get_cari_pelanggan(string $keyword): Collection
    {
        $pola = '%'.addcslashes($keyword, '\\%_').'%';

        return $this->denganStatus(
            $this->pelanggan()
                ->where(fn ($q) => $q->where('nama_lengkap', 'like', $pola)->orWhere('nomor_telepon', 'like', $pola))
                ->get()
        );
    }

    // PC-37
    public function get_data_detail_pelanggan(string $idPelanggan): ?object
    {
        $pelanggan = $this->pelanggan()->where('id_pengguna', $idPelanggan)->first();
        if (! $pelanggan) {
            return null;
        }

        $pelanggan = $this->denganStatus(collect([$pelanggan]))->first();
        $pelanggan->riwayat = DB::table('PEMESANAN as p')
            ->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')
            ->leftJoin('PEMBAYARAN as b', 'b.id_pemesanan', '=', 'p.id_pemesanan')
            ->where('p.FK_id_pengguna', $idPelanggan)
            ->orderByDesc('p.jadwal_keberangkatan')
            ->get(['p.kode_pemesanan', 'k.nama_paket', 'p.jadwal_keberangkatan', 'p.jumlah_peserta', 'p.total_pembayaran', 'b.status as status_pembayaran']);

        return $pelanggan;
    }
}
