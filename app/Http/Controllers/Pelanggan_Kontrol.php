<?php

namespace App\Http\Controllers;

use App\Entities\Pelanggan_Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// CD-21, CD-22: Staff (dan Manager sebagai generalisasi Staff) memeriksa data pelanggan.
class Pelanggan_Kontrol extends Controller
{
    public function __construct(private Pelanggan_Entity $pelangganEntity) {}

    // PC-35
    public function getAll_pelanggan()
    {
        // dataPelanggan = CALL Pelanggan_Entity.getAll_data_pelanggan()
        $dataPelanggan = $this->pelangganEntity->getAll_data_pelanggan();

        // CALL K1_Halaman_Pelanggan.tampilkan_dataPelanggan(dataPelanggan)
        return view('pelanggan.k1_halaman_pelanggan', ['dataPelanggan' => $dataPelanggan, 'keyword' => '']);
    }

    // PC-36
    public function cari_pelanggan(Request $request)
    {
        $keyword = trim((string) $request->validate(['keyword' => ['nullable', 'string', 'max:50']])['keyword'] ?? '');
        if ($keyword === '') {
            return redirect()->route('pelanggan.index');
        }

        // hasilCari = CALL Pelanggan_Entity.get_cari_pelanggan(keyword)
        $hasilCari = $this->pelangganEntity->get_cari_pelanggan($keyword);

        // statusData = CALL Pelanggan_Kontrol.cek_data_pelanggan(hasilCari)
        $statusData = $this->cek_data_pelanggan($hasilCari);

        return view('pelanggan.k1_halaman_pelanggan', [
            'dataPelanggan' => $hasilCari,
            'keyword' => $keyword,
            // CALL K1_Halaman_Pelanggan.tampilkan_pesan("Data Pelanggan Tidak Ditemukan")
            'pesan' => $statusData ? null : 'Data Pelanggan Tidak Ditemukan',
        ]);
    }

    public function cek_data_pelanggan(Collection $hasilCari): bool
    {
        return $hasilCari->isNotEmpty();
    }

    // PC-37
    public function get_detail_pelanggan(string $idPelanggan)
    {
        // detailPelanggan = CALL Pelanggan_Entity.get_data_detail_pelanggan(idPelanggan)
        $detailPelanggan = $this->pelangganEntity->get_data_detail_pelanggan($idPelanggan);
        abort_unless($detailPelanggan, 404);

        // CALL K1_Halaman_Detail_Pelanggan.tampilkan_detailPelanggan(detailPelanggan)
        return view('pelanggan.k1_halaman_detail_pelanggan', compact('detailPelanggan'));
    }
}
