<?php
namespace App\Http\Controllers;

use App\Entities\Driver_Entity;
use App\Entities\Pemesanan_Entity as E;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Driver_Kontrol extends Controller
{
    public function __construct(private Driver_Entity $entity) {}

    public function getDataMaster()
    {
        return view('admin.dashboard', $this->entity->getAllDataMaster());
    }

    public function getDaftarDriver(Request $r)
    {
        $keyword = $r->validate(['keyword' => 'nullable|string|max:100'])['keyword'] ?? '';
        return view('admin.driver.index', ['daftarDriver' => $this->entity->getAllDataDriver($keyword), 'keyword' => $keyword]);
    }

    public function getDriver(?string $id = null)
    {
        return view('admin.driver.form', ['driver' => $id ? $this->entity->getDataDriver($id) : null, 'akunDriver' => $this->entity->akunDriver()]);
    }
    public function validasiTambahDriver(Request $r)
    {
        return $this->validasiEditDriver($r);
    }

    public function validasiEditDriver(Request $r, ?string $id = null)
    {
        if ($id) { $this->entity->getDataDriver($id); }
        $t = 'DRIVER';
        $pk = 'id_driver';
        $d = $r->validate(['nama_lengkap' => 'required|string|max:100', 'nomor_telepon' => 'required|string|max:20', 'nomor_sim' => ['required', 'string', 'max:50', Rule::unique($t)->ignore($id, $pk)], 'alamat' => 'required|string|max:255', 'status' => 'required|in:Tersedia,Bertugas,Tidak Aktif', 'FK_id_pengguna' => ['nullable', Rule::exists('UMUM', 'id_pengguna')->where('role', 'Driver'), Rule::unique('DRIVER', 'FK_id_pengguna')->ignore($id, $pk)], 'email' => 'nullable|email|max:50|unique:UMUM,email', 'password' => 'required_with:email|nullable|min:8|confirmed']);
        $r->validate(['foto_driver' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($r->hasFile('foto_driver')) {
            $d['foto_driver'] = $r->file('foto_driver')->store('driver', 'public');
        }
        $this->entity->simpan($d, $id);
        return redirect()->route('admin.driver.index')->with('success', 'Data berhasil disimpan.');
    }

    public function hapusDriver(string $id)
    {
        $this->entity->hapusDataDriver($id);
        return back()->with('success', 'Data dihapus.');
    }
}
