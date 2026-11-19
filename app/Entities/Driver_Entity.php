<?php
namespace App\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use App\Entities\Pemesanan_Entity as E;

class Driver_Entity
{
    public function getAllDataMaster(): array
    {
        return ['totalPaket' => DB::table('PAKET_TRAVEL')->count(), 'totalKendaraan' => DB::table('KENDARAAN')->count(), 'totalDriver' => DB::table('DRIVER')->count()];
    }

    public function getAllDataDriver(string $keyword = ''): Collection
    {
        return DB::table('DRIVER')->when($keyword, fn ($q) => $q->where('nama_lengkap', 'like', '%'.$keyword.'%'))->get();
    }

    public function getDataDriver(string $id): object
    {
        $item = DB::table('DRIVER')->where('id_driver', $id)->first();
        abort_unless($item, 404);
        return $item;
    }

    public function simpan(array $d, ?string $id = null): bool
    {
        return DB::transaction(function () use ($d, $id) {
            $old = $id ? DB::table('DRIVER')->where('id_driver', $id)->lockForUpdate()->first() : null;
            if ($id) { abort_unless($old, 404); }
            if (!empty($d['email'])) {
                E::check(empty($d['FK_id_pengguna']), 'email', 'Pilih akun atau buat akun baru, bukan keduanya.');
                $uid = E::id();
                DB::table('UMUM')->insert(['id_pengguna' => $uid, 'nama_lengkap' => substr($d['nama_lengkap'], 0, 50), 'email' => $d['email'], 'nomor_telepon' => $d['nomor_telepon'], 'password' => Hash::make($d['password']), 'role' => 'Driver']);
                $d['FK_id_pengguna'] = $uid;
            }
            unset($d['email'], $d['password']);
            if ($id) { DB::table('DRIVER')->where('id_driver', $id)->update($d); }
            else { DB::table('DRIVER')->insert($d + ['id_driver' => E::id()]); }
            return true;
        });
    }

    public function hapusDataDriver(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            abort_unless(DB::table('DRIVER')->where('id_driver', $id)->lockForUpdate()->first(), 404);
            $ref = DB::table('JADWAL_PERJALANAN')->where('id_driver', $id);
            E::check(!$ref->exists(), 'hapus', 'Data masih digunakan.');
            return (bool) DB::table('DRIVER')->where('id_driver', $id)->delete();
        });
    }

    public function akunDriver(): Collection
    {
        return DB::table('UMUM')->where('role', 'Driver')->get();
    }

    public function getDriverTersedia(): Collection
    {
        return DB::table('DRIVER as d')->join('UMUM as u', 'u.id_pengguna', '=', 'd.FK_id_pengguna')->where('u.role', 'Driver')->whereIn('d.status', ['Tersedia', 'Bertugas'])->get(['d.*']);
    }
}
