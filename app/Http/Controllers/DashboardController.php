<?php

namespace App\Http\Controllers;

use App\Entities\JadwalTour_Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function staff()
    {
        $bulanIni = [Carbon::today()->startOfMonth()->toDateString(), Carbon::today()->toDateString()];

        return view('staff.dashboard', [
            'totalPelanggan' => DB::table('UMUM')->where('role', 'Customer')->count(),
            'transaksiBulanIni' => DB::table('PEMBAYARAN')->whereBetween('tanggal_bayar', $bulanIni)->count(),
            'menungguVerifikasi' => DB::table('PEMBAYARAN')->where('status', 'Proses Verifikasi')->count(),
        ]);
    }

    public function manager()
    {
        $lunas = DB::table('PEMBAYARAN')->where('status', 'Lunas');
        $awal = Carbon::today()->startOfMonth()->subMonths(5);

        $perBulan = DB::table('PEMBAYARAN')->where('status', 'Lunas')->where('tanggal_bayar', '>=', $awal->toDateString())
            ->get(['tanggal_bayar', 'nominal'])
            ->groupBy(fn ($b) => substr($b->tanggal_bayar, 0, 7));

        $grafik = collect(range(0, 5))->map(function ($i) use ($awal, $perBulan) {
            $bulan = $awal->copy()->addMonths($i);

            return ['label' => $bulan->translatedFormat('M Y'), 'nilai' => (float) ($perBulan[$bulan->format('Y-m')] ?? collect())->sum('nominal')];
        });

        return view('manager.dashboard', [
            'totalTransaksi' => DB::table('PEMBAYARAN')->count(),
            'pendapatan' => (float) (clone $lunas)->sum('nominal'),
            'pesananLunas' => (clone $lunas)->count(),
            'totalDriver' => DB::table('DRIVER')->count(),
            'grafik' => $grafik,
        ]);
    }

    public function driver(Request $request, JadwalTour_Entity $jadwalTourEntity)
    {
        $jadwal = $jadwalTourEntity->getAll_data_jadwalTour($jadwalTourEntity->idDriver($request->user()->id_pengguna));
        $selesai = $jadwal->where('status', 'Selesai')->count();
        $mendatang = $jadwal->filter(fn ($j) => $j->status !== 'Selesai' && $j->tanggal_berangkat >= Carbon::today()->toDateString());

        return view('driver.dashboard', [
            'total' => $jadwal->count(),
            'bulanIni' => $jadwal->filter(fn ($j) => Carbon::parse($j->tanggal_berangkat)->isSameMonth(Carbon::today()))->count(),
            'selesai' => $selesai,
            'mendatang' => $mendatang->count(),
            'persen' => $jadwal->count() ? (int) round($selesai / $jadwal->count() * 100) : 0,
            'terdekat' => $mendatang->take(3),
        ]);
    }
}
