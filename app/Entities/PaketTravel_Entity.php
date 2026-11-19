<?php
namespace App\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use App\Entities\Pemesanan_Entity as E;

class PaketTravel_Entity
{
    public function getAllDataMaster(): array
    {
        return ['totalPaket' => DB::table('PAKET_TRAVEL')->count(), 'totalKendaraan' => DB::table('KENDARAAN')->count(), 'totalDriver' => DB::table('DRIVER')->count()];
    }

    public function getAllDataPaketTravel(string $keyword = ''): Collection
    {
        return DB::table('PAKET_TRAVEL')->when($keyword, fn ($q) => $q->where('nama_paket', 'like', '%'.$keyword.'%'))->get();
    }

    public function getDataPaketTravel(string $id): object
    {
        $item = DB::table('PAKET_TRAVEL')->where('id_paket', $id)->first();
        abort_unless($item, 404);
        return $item;
    }

    public function simpan(array $d, ?string $id = null): bool
    {
        return DB::transaction(function () use ($d, $id) {
            $old = $id ? DB::table('PAKET_TRAVEL')->where('id_paket', $id)->lockForUpdate()->first() : null;
            if ($id) { abort_unless($old, 404); }
            $reserved = $id ? (new E)->reserved($id) : 0;
            E::check($d['kuota'] >= $reserved, 'kuota', 'Kuota lebih kecil dari reservasi.');
            E::check(!$reserved || $d['jadwal_keberangkatan'] === $old->jadwal_keberangkatan, 'jadwal_keberangkatan', 'Jadwal memiliki reservasi aktif.');
            if (!empty($d['foto_paket'])) { $d['foto_paket'] = $d['foto_paket']->store('paket', 'local'); }
            else { unset($d['foto_paket']); }
            $d['biaya_admin'] = $d['biaya_admin'] ?? 0;
            if ($id) { DB::table('PAKET_TRAVEL')->where('id_paket', $id)->update($d); }
            else { DB::table('PAKET_TRAVEL')->insert($d + ['id_paket' => E::id()]); }
            return true;
        });
    }

    public function hapusDataPaketTravel(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            abort_unless(DB::table('PAKET_TRAVEL')->where('id_paket', $id)->lockForUpdate()->first(), 404);
            $ref = DB::table('PEMESANAN')->where('FK_id_paket', $id);
            E::check(!$ref->exists(), 'hapus', 'Data masih digunakan.');
            return (bool) DB::table('PAKET_TRAVEL')->where('id_paket', $id)->delete();
        });
    }

    // CD-04 spells this method Pkaket; SD-07 uses Paket. Both reach the same guarded deletion.
    public function hapusDataPkaketTravel(string $id): bool
    {
        return $this->hapusDataPaketTravel($id);
    }
}
