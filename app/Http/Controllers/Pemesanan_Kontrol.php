<?php

namespace App\Http\Controllers;

use App\Entities\Pemesanan_Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Pemesanan_Kontrol extends Controller
{
    public function __construct(private Pemesanan_Entity $entity) {}

    public function create(string $idPaket)
    {
        return view('customer.pemesanan.create', ['paket' => $this->entity->paket($idPaket)]);
    }

    private function data(Request $r, string $id): array
    {
        $p = $this->entity->paket($id);
        $d = $r->validate(['jadwal_keberangkatan' => 'required|date|after_or_equal:today|in:'.$p->jadwal_keberangkatan, 'jumlah_peserta' => 'required|integer|min:1|max:100']);
        Pemesanan_Entity::check($d['jumlah_peserta'] <= $p->sisa_kuota, 'jumlah_peserta', 'Kuota tidak mencukupi.');

        return $d;
    }

    public function penumpang(Request $r, string $idPaket)
    {
        $data = $this->data($r, $idPaket);
        $paket = $this->entity->paket($idPaket);
        $totalPembayaran = $paket->harga * $data['jumlah_peserta'] + $paket->biaya_admin;

        return view('customer.pemesanan.penumpang', compact('data', 'paket', 'totalPembayaran'));
    }

    public function store(Request $r, string $idPaket)
    {
        $d = $this->data($r, $idPaket);
        $contact = $r->validate(['email' => 'required|email|max:50', 'no_hp' => 'required|string|max:50', 'penumpang' => 'required|array|size:'.$d['jumlah_peserta'], 'penumpang.*.nama_lengkap' => 'required|string|max:100', 'penumpang.*.nik' => 'required|digits:16|distinct', 'penumpang.*.tanggal_lahir' => 'required|date|before_or_equal:today']);
        $contact['penumpang'] = json_encode($contact['penumpang']);
        $id = $this->entity->store($d + $contact + ['FK_id_paket' => $idPaket], $r->user()->id_pengguna);

        return redirect()->route('customer.pesanan.detail', $id)->with('success', 'Pemesanan berhasil dibuat.');
    }

    public function index(Request $r)
    {
        $this->entity->expire();

        return view('customer.pemesanan.index', ['daftarPemesanan' => $this->entity->daftar()->where('p.FK_id_pengguna', $r->user()->id_pengguna)->orderByDesc('p.jadwal_keberangkatan')->get()]);
    }

    public function detail(Request $r, string $id)
    {
        return view('customer.pemesanan.detail', ['detailPemesanan' => $this->entity->detail($id, $r->user()->id_pengguna)]);
    }

    public function staffIndex(Request $r)
    {
        $this->entity->expire();
        $status = $r->validate(['status' => 'nullable|string|max:50'])['status'] ?? '';

        return view('staff.pemesanan.index', ['daftarPemesanan' => $this->entity->daftar()->when($status, fn ($q) => $q->where('p.status', $status))->get(), 'status' => $status]);
    }

    public function staffDetail(string $id)
    {
        return view('staff.pemesanan.detail', ['detailPemesanan' => $this->entity->detail($id)]);
    }

    public function verifikasi(Request $r, string $id)
    {
        $d = $r->validate(['link_grup_wa' => ['nullable', 'url', 'max:255', 'regex:~^https://chat\.whatsapp\.com/[A-Za-z0-9]+$~']]);

        return $this->decision($id, function ($o) use ($d) {
            Pemesanan_Entity::check(DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->where('status', 'Lunas')->exists(), 'status', 'Pembayaran belum terverifikasi.');

            return $d + ['status' => 'Terkonfirmasi'];
        }, true);
    }

    public function tolak(Request $r, string $id)
    {
        $d = $r->validate(['alasan' => 'required|string|max:1000']);

        return $this->decision($id, fn ($o) => $d + ['status' => 'Ditolak', 'status_kuota' => 'Kuota Dilepas']);
    }

    private function decision(string $id, callable $change, bool $allowConfirmed = false)
    {
        DB::transaction(function () use ($id, $change, $allowConfirmed) {
            $o = DB::table('PEMESANAN')->where('id_pemesanan', $id)->lockForUpdate()->first();
            abort_unless($o, 404);
            Pemesanan_Entity::check(! in_array($o->status, ['Dibatalkan', 'Ditolak']) && ($allowConfirmed || $o->status !== 'Terkonfirmasi'), 'status', 'Pemesanan tidak dapat diubah.');
            DB::table('PEMESANAN')->where('id_pemesanan',$id)->update($change($o));
        });

        return back()->with('success','Pemesanan diperbarui.');
    }
}
