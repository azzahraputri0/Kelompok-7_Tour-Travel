<?php
namespace App\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use App\Entities\Pemesanan_Entity as E;

class Kendaraan_Entity
{
    public function getAllDataMaster(): array
    {
        return ['totalPaket' => DB::table('PAKET_TRAVEL')->count(), 'totalKendaraan' => DB::table('KENDARAAN')->count(), 'totalDriver' => DB::table('DRIVER')->count()];
    }

    public function getAllDataKendaraan(string $keyword = ''): Collection
    {
        return DB::table('KENDARAAN')->when($keyword, fn ($q) => $q->where('plat_nomor', 'like', '%'.$keyword.'%'))->get();
    }

    public function getDataKendaraan(string $id): object
    {
        $item = DB::table('KENDARAAN')->where('id_kendaraan', $id)->first();
        abort_unless($item, 404);
        return $item;
    }

    public function simpan(array $d, ?string $id = null): bool
    {
        return DB::transaction(function () use ($d, $id) {
            $old = $id ? DB::table('KENDARAAN')->where('id_kendaraan', $id)->lockForUpdate()->first() : null;
            if ($id) { abort_unless($old, 404); }
            if ($id) { DB::table('KENDARAAN')->where('id_kendaraan', $id)->update($d); }
            else { DB::table('KENDARAAN')->insert($d + ['id_kendaraan' => E::id()]); }
            return true;
        });
    }

    public function hapusDataKendaraan(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            abort_unless(DB::table('KENDARAAN')->where('id_kendaraan', $id)->lockForUpdate()->first(), 404);
            $ref = DB::table('JADWAL_PERJALANAN')->where('id_kendaraan', $id);
            E::check(!$ref->exists(), 'hapus', 'Data masih digunakan.');
            return (bool) DB::table('KENDARAAN')->where('id_kendaraan', $id)->delete();
        });
    }
}
