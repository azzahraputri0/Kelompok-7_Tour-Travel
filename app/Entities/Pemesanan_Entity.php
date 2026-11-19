<?php

namespace App\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Pemesanan_Entity
{
    public static function id(): string
    {
        return (string) Str::uuid();
    }

    public static function check(bool $ok, string $key, string $message): void
    {
        if (! $ok) {
            throw ValidationException::withMessages([$key => $message]);
        }
    }

    public function expire(): int
    {
        return DB::table('PEMESANAN')->where('status', 'Belum Terbayar')->whereNotNull('kedaluwarsa_pada')->where('kedaluwarsa_pada', '<', now())->update(['status' => 'Dibatalkan', 'status_kuota' => 'Kuota Dilepas']);
    }

    public function reserved(string $id): int
    {
        return (int) DB::table('PEMESANAN')->where('FK_id_paket', $id)->where('status_kuota', 'Kuota Terkunci')->whereNotIn('status', ['Dibatalkan', 'Ditolak'])->sum('jumlah_peserta');
    }

    public function paket(string $id): object
    {
        $this->expire();
        $p = DB::table('PAKET_TRAVEL')->where('id_paket', $id)->first();
        abort_unless($p, 404);
        $p->sisa_kuota = max(0, $p->kuota - $this->reserved($id));

        return $p;
    }

    public function daftar()
    {
        return DB::table('PEMESANAN as p')->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')->join('UMUM as u', 'u.id_pengguna', '=', 'p.FK_id_pengguna')->select('p.*', 'k.nama_paket', 'k.tujuan', 'u.nama_lengkap as nama_customer')->selectSub(DB::table('PEMBAYARAN as b')->select('status')->whereColumn('b.id_pemesanan', 'p.id_pemesanan')->orderByDesc('tanggal_bayar')->limit(1), 'status_pembayaran');
    }

    public function detail(string $id, ?string $owner = null): object
    {
        $this->expire();
        $q = $this->daftar()->where('p.id_pemesanan', $id);
        if ($owner) {
            $q->where('p.FK_id_pengguna', $owner);
        }$o = $q->first();
        abort_unless($o, 404);
        $o->penumpang = json_decode($o->penumpang ?? '[]', true);
        $o->pembayaran = DB::table('PEMBAYARAN')->where('id_pemesanan', $id)->first();

        return $o;
    }

    public function store(array $d, string $owner): string
    {
        return DB::transaction(function () use ($d, $owner) {
            DB::table('PAKET_TRAVEL')->where('id_paket', $d['FK_id_paket'])->lockForUpdate()->first();
            $p = $this->paket($d['FK_id_paket']);
            self::check($d['jumlah_peserta'] <= $p->sisa_kuota, 'jumlah_peserta', 'Kuota tidak mencukupi.');
            $id = self::id();
            DB::table('PEMESANAN')->insert($d + ['id_pemesanan' => $id, 'kode_pemesanan' => 'TRV-'.strtoupper(substr($id, 0, 8)), 'FK_id_pengguna' => $owner, 'total_pembayaran' => $p->harga * $d['jumlah_peserta'] + $p->biaya_admin, 'status' => 'Belum Terbayar', 'status_kuota' => 'Kuota Terkunci', 'batas_waktu_pembayaran' => now()->addDay()->toDateString(), 'kedaluwarsa_pada' => now()->addDay()]);

            return $id;
        });
    }
}
