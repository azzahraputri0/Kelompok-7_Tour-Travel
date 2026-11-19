<?php

namespace App\Http\Controllers;

use App\Entities\Pemesanan_Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Pembayaran_Kontrol extends Controller
{
    public function __construct(private Pemesanan_Entity $entity) {}

    public function create(Request $r, string $id)
    {
        $qrisImage = null;
        $configured = (string) config('travel.qris_image', '');
        if (preg_match('#^/?[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*\.(?:png|jpe?g|webp)$#i', $configured)) {
            $path = realpath(public_path(ltrim($configured, '/')));
            $roots = [realpath(public_path()), realpath(storage_path('app/public'))];
            foreach ($roots as $root) {
                if ($path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path)
                    && in_array(mime_content_type($path), ['image/png', 'image/jpeg', 'image/webp'], true)) {
                    $qrisImage = '/'.ltrim($configured, '/');
                    break;
                }
            }
        }

        return view('customer.pembayaran.create', [
            'detailPemesanan' => $this->entity->detail($id, $r->user()->id_pengguna),
            'qrisImage' => $qrisImage,
        ]);
    }

    public function store(Request $r, string $id)
    {
        $r->validate(['bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096']);
        $this->entity->detail($id, $r->user()->id_pengguna);
        $path = null;
        try {
            DB::transaction(function () use ($r, $id, &$path) {
                $o = DB::table('PEMESANAN')->where('id_pemesanan', $id)->lockForUpdate()->first();
                Pemesanan_Entity::check($o->status === 'Belum Terbayar' && (! $o->kedaluwarsa_pada || now()->lte($o->kedaluwarsa_pada)), 'status', 'Pemesanan tidak dapat dibayar.');
                $old = DB::table('PEMBAYARAN')->where('id_pemesanan', $id)->first();
                Pemesanan_Entity::check(! $old || $old->status === 'Ditolak', 'status', 'Pembayaran sudah dikirim.');
                $path = $r->file('bukti_pembayaran')->store('pembayaran', 'local');
                $d = ['metode_pembayaran' => 'QRIS', 'nominal' => $o->total_pembayaran, 'tanggal_bayar' => today()->toDateString(), 'bukti_pembayaran' => $path, 'status' => 'Proses Verifikasi', 'alasan' => null];
                if ($old) {
                    DB::table('PEMBAYARAN')->where('id_pembayaran', $old->id_pembayaran)->update($d);
                } else {
                    DB::table('PEMBAYARAN')->insert($d + ['id_pembayaran' => Pemesanan_Entity::id(), 'id_pemesanan' => $id, 'nomor_invoice' => 'INV-'.$o->kode_pemesanan]);
                }DB::table('PEMESANAN')->where('id_pemesanan', $id)->update(['status' => 'Menunggu Verifikasi']);
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }throw $e;
        }

return redirect()->route('customer.pesanan.detail', $id)->with('success', 'Bukti pembayaran dikirim.');
    }

    private function query()
    {
        return DB::table('PEMBAYARAN as b')->join('PEMESANAN as p', 'p.id_pemesanan', '=', 'b.id_pemesanan')->join('UMUM as u', 'u.id_pengguna', '=', 'p.FK_id_pengguna')->join('PAKET_TRAVEL as k', 'k.id_paket', '=', 'p.FK_id_paket')->select('b.*', 'p.kode_pemesanan', 'p.total_pembayaran', 'p.FK_id_pengguna', 'u.nama_lengkap as nama_customer', 'k.nama_paket');
    }

    public function index(Request $r)
    {
        $status = $r->validate(['status' => 'nullable|string|max:50'])['status'] ?? '';

        return view('staff.pembayaran.index', ['daftarPembayaran' => $this->query()->when($status, fn ($q) => $q->where('b.status', $status))->get(), 'status' => $status]);
    }

    public function detail(string $id)
    {
        $pembayaran = $this->query()->where('b.id_pembayaran', $id)->first();
        abort_unless($pembayaran, 404);

        return view('staff.pembayaran.detail', compact('pembayaran'));
    }

    public function verifikasi(string $id)
    {
        return $this->decision($id, 'Lunas', null);
    }

    public function tolak(Request $r, string $id)
    {
        return $this->decision($id, 'Ditolak', $r->validate(['alasan' => 'required|string|max:1000'])['alasan']);
    }

    private function decision(string $id, string $status, ?string $alasan)
    {
        DB::transaction(function () use ($id, $status, $alasan) {
            $p = DB::table('PEMBAYARAN')->where('id_pembayaran', $id)->first();
            abort_unless($p, 404);
            $o = DB::table('PEMESANAN')->where('id_pemesanan', $p->id_pemesanan)->lockForUpdate()->first();
            $p = DB::table('PEMBAYARAN')->where('id_pembayaran', $id)->lockForUpdate()->first();
            Pemesanan_Entity::check($p->status === 'Proses Verifikasi' && ! in_array($o->status, ['Ditolak', 'Dibatalkan', 'Terkonfirmasi']), 'status', 'Pembayaran tidak dapat diubah.');
            DB::table('PEMBAYARAN')->where('id_pembayaran', $id)->update(compact('status', 'alasan'));
            DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['status' => $status === 'Lunas' ? 'Terkonfirmasi' : 'Belum Terbayar']);
        });

        return back()->with('success', 'Pembayaran diperbarui.');
    }

    public function bukti(Request $r, string $id)
    {
        $p = $this->query()->where('b.id_pembayaran', $id)->first();
        abort_unless($p && ($r->user()->role === 'Staff' || $p->FK_id_pengguna === $r->user()->id_pengguna), 404);
        abort_unless(str_starts_with($p->bukti_pembayaran ?? '', 'pembayaran/') && Storage::disk('local')->exists($p->bukti_pembayaran), 404);

        return Storage::disk('local')->response($p->bukti_pembayaran, null, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function identitas(Request $r, string $id)
    {
        $u = DB::table('UMUM')->where('id_pengguna', $id)->first();
        abort_unless($u && ($id === $r->user()->id_pengguna || in_array($r->user()->role, ['Staff', 'Manager'])), 404);
        abort_unless(str_starts_with($u->dokumen_identitas ?? '','identitas/') && Storage::disk('local')->exists($u->dokumen_identitas),404);

        return Storage::disk('local')->response($u->dokumen_identitas,null,['X-Content-Type-Options' => 'nosniff']);
    }
}
