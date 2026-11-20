<?php

namespace App\Http\Controllers;

use App\Entities\Pemesanan_Entity as E;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Penugasan_Kontrol extends Controller
{
    public function index()
    {
        return view('admin.penugasan.index', ['daftarPenugasan' => DB::table('JADWAL_PERJALANAN as j')->join('PEMESANAN as p', 'p.id_pemesanan', '=', 'j.id_pemesanan')->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')->join('DRIVER as d', 'd.id_driver', '=', 'j.id_driver')->join('KENDARAAN as v', 'v.id_kendaraan', '=', 'j.id_kendaraan')->join('UMUM as u', 'u.id_pengguna', '=', 'p.FK_id_pengguna')->select('j.*', 'p.kode_pemesanan', 'k.nama_paket', 'd.nama_lengkap as nama_driver', 'v.plat_nomor', 'u.nama_lengkap as nama_customer')->get(), 'daftarPemesanan' => (new E)->daftar()->where('p.status', 'Terkonfirmasi')->whereNotIn('p.id_pemesanan', DB::table('JADWAL_PERJALANAN')->select('id_pemesanan'))->get(), 'daftarDriver' => (new \App\Entities\Driver_Entity)->getDriverTersedia(), 'daftarKendaraan' => DB::table('KENDARAAN')->where('status', 'Tersedia')->get()]);
    }

    private function end(string $date, string $duration): string
    {
        preg_match('/\d+/', $duration, $m);

        return Carbon::parse($date)->addDays(max(1, (int) ($m[0] ?? 1)) - 1)->toDateString();
    }

    public function store(Request $r)
    {
        $d = $r->validate(['id_pemesanan' => 'required|exists:PEMESANAN,id_pemesanan', 'id_driver' => 'required|exists:DRIVER,id_driver', 'id_kendaraan' => 'required|exists:KENDARAAN,id_kendaraan', 'tanggal_berangkat' => 'nullable|date']);
        DB::transaction(function () use ($d) {
            $o = DB::table('PEMESANAN')->where('id_pemesanan', $d['id_pemesanan'])->lockForUpdate()->first();
            $v = DB::table('KENDARAAN')->where('id_kendaraan', $d['id_kendaraan'])->lockForUpdate()->first();
            $driver = DB::table('DRIVER')->where('id_driver', $d['id_driver'])->lockForUpdate()->first();
            $account = DB::table('UMUM')->where('id_pengguna', $driver->FK_id_pengguna)->where('role', 'Driver')->lockForUpdate()->first();
            E::check((bool) $account, 'id_driver', 'Driver harus memiliki akun Driver yang valid.');
            E::check($o->status === 'Terkonfirmasi' && $o->jadwal_keberangkatan >= today()->toDateString(), 'id_pemesanan', 'Pemesanan belum dikonfirmasi atau sudah lewat.');
            E::check(! DB::table('JADWAL_PERJALANAN')->where('id_pemesanan', $o->id_pemesanan)->exists(), 'id_pemesanan', 'Pemesanan sudah ditugaskan.');
            E::check($v->status === 'Tersedia' && in_array($driver->status, ['Tersedia', 'Bertugas']), 'id_driver', 'Sumber daya tidak tersedia.');
            E::check($v->kapasitas >= $o->jumlah_peserta, 'id_kendaraan', 'Kapasitas kendaraan tidak mencukupi.');
            E::check(empty($d['tanggal_berangkat']) || $d['tanggal_berangkat'] === $o->jadwal_keberangkatan, 'tanggal_berangkat', 'Tanggal harus sesuai pemesanan.');
            $dur = DB::table('PAKET_TRAVEL')->where('id_paket', $o->FK_id_paket)->value('durasi');
            $end = $this->end($o->jadwal_keberangkatan, $dur);
            $existing = DB::table('JADWAL_PERJALANAN as j')->join('PEMESANAN as p', 'p.id_pemesanan', '=', 'j.id_pemesanan')->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')->where('j.status', '!=', 'Dibatalkan')->where(fn ($q) => $q->where('j.id_driver', $d['id_driver'])->orWhere('j.id_kendaraan', $d['id_kendaraan']))->get(['j.tanggal_berangkat', 'k.durasi']);
            foreach ($existing as $j) {
                E::check($j->tanggal_berangkat > $end || $this->end($j->tanggal_berangkat, $j->durasi) < $o->jadwal_keberangkatan, 'id_driver', 'Driver atau kendaraan memiliki jadwal bentrok.');
            }DB::table('JADWAL_PERJALANAN')->insert(['id_jadwal' => E::id(), 'id_pemesanan' => $o->id_pemesanan, 'id_driver' => $d['id_driver'], 'id_kendaraan' => $d['id_kendaraan'], 'tanggal_berangkat' => $o->jadwal_keberangkatan, 'status' => 'Terjadwal']);
        });

        return back()->with('success', 'Penugasan disimpan.');
    }

    public function destroy(string $id)
    {
        $j = DB::table('JADWAL_PERJALANAN')->where('id_jadwal', $id)->first();
        abort_unless($j, 404);
        E::check($j->status === 'Terjadwal' && $j->tanggal_berangkat > today()->toDateString(),'status','Penugasan tidak dapat dibatalkan.');
        DB::table('JADWAL_PERJALANAN')->where('id_jadwal',$id)->delete();

        return back()->with('success','Penugasan dibatalkan.');
    }
}
