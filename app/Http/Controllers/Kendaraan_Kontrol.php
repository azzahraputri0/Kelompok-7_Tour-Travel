<?php
namespace App\Http\Controllers;

use App\Entities\Kendaraan_Entity;
use App\Entities\Pemesanan_Entity as E;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Kendaraan_Kontrol extends Controller
{
    public function __construct(private Kendaraan_Entity $entity) {}

    public function getDataMaster()
    {
        return view('admin.dashboard', $this->entity->getAllDataMaster());
    }

    public function getDaftarKendaraan(Request $r)
    {
        $keyword = $r->validate(['keyword' => 'nullable|string|max:100'])['keyword'] ?? '';
        return view('admin.kendaraan.index', ['daftarKendaraan' => $this->entity->getAllDataKendaraan($keyword), 'keyword' => $keyword]);
    }

    public function getKendaraan(?string $id = null)
    {
        return view('admin.kendaraan.form', ['kendaraan' => $id ? $this->entity->getDataKendaraan($id) : null]);
    }
    public function validasiTambahKendaraan(Request $r)
    {
        return $this->validasiEditKendaraan($r);
    }

    public function validasiEditKendaraan(Request $r, ?string $id = null)
    {
        if ($id) { $this->entity->getDataKendaraan($id); }
        $t = 'KENDARAAN';
        $pk = 'id_kendaraan';
        $d = $r->validate(['plat_nomor' => ['required', 'string', 'max:20', Rule::unique($t)->ignore($id, $pk)], 'jenis_kendaraan' => 'required|string|max:50', 'merek' => 'required|string|max:50', 'kapasitas' => 'required|integer|min:1|max:100', 'tahun_produksi' => 'required|integer|min:1980|max:'.(date('Y') + 1), 'status' => 'required|in:Tersedia,Perawatan,Tidak Aktif']);
        $r->validate(['foto_kendaraan' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($r->hasFile('foto_kendaraan')) {
            $d['foto_kendaraan'] = $r->file('foto_kendaraan')->store('kendaraan', 'public');
        }
        $this->entity->simpan($d, $id);
        return redirect()->route('admin.kendaraan.index')->with('success', 'Data berhasil disimpan.');
    }

    public function hapusKendaraan(string $id)
    {
        $this->entity->hapusDataKendaraan($id);
        return back()->with('success', 'Data dihapus.');
    }
}
