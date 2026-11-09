<?php

namespace App\Http\Controllers;

use App\Entities\JadwalTour_Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// CD-26: Driver melihat jadwal tour miliknya.
class JadwalTour_Kontrol extends Controller
{
    public function __construct(private JadwalTour_Entity $jadwalTourEntity) {}

    // PC-40
    public function getAll_jadwalTour(Request $request)
    {
        $idDriver = $this->jadwalTourEntity->idDriver($request->user()->id_pengguna);

        // daftarJadwalTour = CALL JadwalTour_Entity.getAll_data_jadwalTour(idDriver)
        $daftarJadwalTour = $this->jadwalTourEntity->getAll_data_jadwalTour($idDriver);

        // CALL JadwalTour_Kontrol.cek_jadwalTour()
        $ada = $this->cek_jadwalTour($daftarJadwalTour);

        // CALL P1_Halaman_Jadwal_Tour.tampilkan_jadwalTour() / tampilkan_pesan("Jadwal Tour tidak tersedia")
        return view('driver.p1_halaman_jadwal_tour', [
            'daftarJadwalTour' => $daftarJadwalTour,
            'pesan' => $ada ? null : 'Jadwal Tour tidak tersedia',
        ]);
    }

    public function cek_jadwalTour(Collection $daftarJadwalTour): bool
    {
        return $daftarJadwalTour->isNotEmpty();
    }

    // PC-41
    public function get_detail_jadwalTour(Request $request, string $idJadwal)
    {
        $idDriver = $this->jadwalTourEntity->idDriver($request->user()->id_pengguna);

        // detailJadwalTour = CALL JadwalTour_Entity.get_data_detail_jadwalTour(idJadwal)
        $detailJadwalTour = $this->jadwalTourEntity->get_data_detail_jadwalTour($idJadwal, $idDriver);
        abort_unless($detailJadwalTour, 404);

        // CALL P2_Halaman_Detail_Jadwal_Tour.tampilkan_detail_jadwalTour(detailJadwalTour)
        return view('driver.p2_halaman_detail_jadwal_tour', compact('detailJadwalTour'));
    }
}
