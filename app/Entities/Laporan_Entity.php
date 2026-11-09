<?php

namespace App\Entities;

use App\Mail\LaporanTransaksiMail;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

// CD-20, CD-27, CD-28: entity Laporan (sumber data PEMBAYARAN + PEMESANAN + UMUM + PAKET_TRAVEL).
class Laporan_Entity
{
    private function transaksi(): Builder
    {
        return DB::table('PEMBAYARAN as b')
            ->join('PEMESANAN as p', 'p.id_pemesanan', '=', 'b.id_pemesanan')
            ->join('UMUM as u', 'u.id_pengguna', '=', 'p.FK_id_pengguna')
            ->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')
            ->select([
                'b.id_pembayaran', 'b.nomor_invoice', 'b.tanggal_bayar', 'b.nominal', 'b.status as status_pembayaran',
                'p.kode_pemesanan', 'p.jumlah_peserta', 'u.nama_lengkap as nama_customer', 'k.nama_paket', 'k.tujuan',
            ])
            ->orderBy('b.tanggal_bayar')
            ->orderBy('b.id_pembayaran');
    }

    // PC-31, PC-42
    public function getAll_data_transaksi(): Collection
    {
        return $this->transaksi()->get();
    }

    // PC-32, PC-33, PC-43, PC-44, PC-45
    public function get_data_transaksi(?string $jenis, string $periodeAwal, string $periodeAkhir): Collection
    {
        return $this->transaksi()->whereBetween('b.tanggal_bayar', [$periodeAwal, $periodeAkhir])->get();
    }

    // PC-34: kirim laporan ke seluruh akun Manager, mengembalikan statusKirim.
    public function kirim_data_laporan_transaksi(Collection $daftarTransaksi, array $periode): bool
    {
        $emailManager = DB::table('UMUM')->where('role', 'Manager')->pluck('email');
        if ($emailManager->isEmpty() || $daftarTransaksi->isEmpty()) {
            return false;
        }

        try {
            Mail::to($emailManager->all())->send(
                new LaporanTransaksiMail($daftarTransaksi, $periode, self::ringkasan($daftarTransaksi))
            );

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    // Rekap kartu ringkasan UI-27: hanya pembayaran berstatus Lunas yang dihitung.
    public static function ringkasan(Collection $daftarTransaksi): array
    {
        $lunas = $daftarTransaksi->where('status_pembayaran', 'Lunas');

        return [
            'jumlah_lunas' => $lunas->count(),
            'total_peserta' => (int) $lunas->sum('jumlah_peserta'),
            'total_transaksi' => (float) $lunas->sum('nominal'),
        ];
    }
}
