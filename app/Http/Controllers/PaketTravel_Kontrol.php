<?php
namespace App\Http\Controllers;

use App\Entities\PaketTravel_Entity;
use App\Entities\Pemesanan_Entity as E;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PaketTravel_Kontrol extends Controller
{
    public function __construct(private PaketTravel_Entity $entity) {}

    public function getDataMaster()
    {
        return view('admin.dashboard', $this->entity->getAllDataMaster());
    }

    public function getDaftarPaketTravel(Request $r)
    {
        $keyword = $r->validate(['keyword' => 'nullable|string|max:100'])['keyword'] ?? '';
        return view('admin.paket.index', ['daftarPaket' => $this->entity->getAllDataPaketTravel($keyword), 'keyword' => $keyword]);
    }

    public function getPaketTravel(?string $id = null)
    {
        return view('admin.paket.form', ['paket' => $id ? $this->entity->getDataPaketTravel($id) : null]);
    }
    public function validasiPaket(Request $r, ?string $id = null)
    {
        if ($id) { $this->entity->getDataPaketTravel($id); }
        $t = 'PAKET_TRAVEL';
        $pk = 'id_paket';
            $date = $r->input('jadwal_keberangkatan');
            if (is_string($date) && preg_match('~^(\d{2})/(\d{2})/(\d{4})$~D', $date, $parts)) {
                E::check(checkdate((int) $parts[2], (int) $parts[1], (int) $parts[3]), 'jadwal_keberangkatan', 'Tanggal tidak valid.');
                $r->merge(['jadwal_keberangkatan' => $parts[3].'-'.$parts[2].'-'.$parts[1]]);
            }
        $d = $r->validate(['nama_paket' => 'required|string|max:50', 'tujuan' => 'required|string|max:50', 'durasi' => 'required|string|max:50', 'harga' => 'required|numeric|min:1|max:99999999', 'kuota' => 'required|integer|min:1|max:10000', 'jadwal_keberangkatan' => 'required|date_format:Y-m-d|after_or_equal:today', 'deskripsi' => 'nullable|string|max:10000', 'fasilitas' => 'nullable|string|max:10000', 'biaya_admin' => 'nullable|numeric|min:0|max:1000000', 'foto_paket' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096']);
        $this->entity->simpan($d, $id);
        return redirect()->route('admin.paket.index')->with('success', 'Data berhasil disimpan.');
    }

    public function hapusPaket(string $id)
    {
        $this->entity->hapusDataPkaketTravel($id);
        return back()->with('success', 'Data dihapus.');
    }
    public function index(Request $r)
    {
        $keyword = $r->validate(['keyword' => 'nullable|string|max:100'])['keyword'] ?? '';
        (new E)->expire();
        $daftarPaket = $this->entity->getAllDataPaketTravel($keyword)->sortBy('jadwal_keberangkatan')->values()->map(function ($p) {
            $p->sisa_kuota = max(0, $p->kuota - (new E)->reserved($p->id_paket));

            return $p;
        });

        return view('katalog.index', compact('daftarPaket', 'keyword'));
    }

    public function detail(string $id)
    {
        return view('katalog.detail', ['paket' => (new E)->paket($id)]);
    }

    public function foto(string $id)
    {
        $p = (new E)->paket($id);
        abort_unless(str_starts_with($p->foto_paket ?? '', 'paket/') && Storage::disk('local')->exists($p->foto_paket), 404);

        return Storage::disk('local')->response($p->foto_paket);
    }
}
