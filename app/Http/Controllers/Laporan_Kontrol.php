<?php

namespace App\Http\Controllers;

use App\Entities\Laporan_Entity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// CD-20 (Staff: lihat & kirim laporan), CD-27 (Manager: tinjau laporan), CD-28 (Manager: ekspor laporan).
class Laporan_Kontrol extends Controller
{
    public function __construct(private Laporan_Entity $laporanEntity) {}

    private function halaman(Request $request): string
    {
        return $request->user()->role === 'Manager' ? 'manager.q1_halaman_laporan' : 'staff.j1_halaman_laporan';
    }

    private function validasiPeriode(Request $request): array
    {
        // Input tanggal tampil dd/mm/yyyy sesuai Figma; ubah ke Y-m-d sebelum validasi.
        foreach (['periode_awal', 'periode_akhir'] as $kolom) {
            $nilai = (string) $request->input($kolom);
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $nilai, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
                $request->merge([$kolom => sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1])]);
            }
        }

        return $request->validate([
            'jenis' => ['required', 'in:Harian,Mingguan,Bulanan'],
            'periode_awal' => ['required', 'date'],
            'periode_akhir' => ['required', 'date', 'after_or_equal:periode_awal'],
        ], [
            'jenis.required' => 'Jenis periode wajib dipilih.',
            'periode_awal.required' => 'Tanggal awal wajib diisi.',
            'periode_akhir.required' => 'Tanggal akhir wajib diisi.',
            'periode_akhir.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);
    }

    // PC-31 (Staff), PC-42 (Manager): default ke tanggal transaksi terakhir.
    public function getAll_transaksi(Request $request)
    {
        if ($request->has('periode_awal')) {
            return $this->get_transaksi($request);
        }

        // CALL Laporan_Entity.getAll_data_transaksi()
        $daftarTransaksi = $this->laporanEntity->getAll_data_transaksi();

        $tanggal = $daftarTransaksi->max('tanggal_bayar') ?? Carbon::today()->toDateString();
        $daftarTransaksi = $this->laporanEntity->get_data_transaksi('Harian', $tanggal, $tanggal);

        // CALL J1/Q1_Halaman_laporan.tampilkan_halaman_laporan()
        return view($this->halaman($request), [
            'periode' => [
                'jenis' => 'Harian',
                'periode_awal' => $tanggal,
                'periode_akhir' => $tanggal,
            ],
            'daftarTransaksi' => $daftarTransaksi,
            'ringkasan' => Laporan_Entity::ringkasan($daftarTransaksi),
            'pesan' => $daftarTransaksi->isEmpty() ? 'Laporan Transaksi Tidak tersedia' : null,
            'jumlahSeluruhTransaksi' => $daftarTransaksi->count(),
        ]);
    }

    // PC-32, PC-33 (Staff), PC-43, PC-44 (Manager)
    public function get_transaksi(Request $request)
    {
        $periode = $this->validasiPeriode($request);

        // CALL Laporan_Entity.get_data_transaksi(jenis, periodeAwal, periodeAkhir)
        $daftarTransaksi = $this->laporanEntity->get_data_transaksi($periode['jenis'], $periode['periode_awal'], $periode['periode_akhir']);

        // CALL Laporan_Kontrol.cek_transaksi(daftarTransaksi)
        $ada = $this->cek_transaksi($daftarTransaksi);

        return view($this->halaman($request), [
            'periode' => $periode,
            'daftarTransaksi' => $daftarTransaksi,
            'ringkasan' => Laporan_Entity::ringkasan($daftarTransaksi),
            // CALL tampilkan_pesan("Laporan Transaksi Tidak tersedia")
            'pesan' => $ada ? null : 'Laporan Transaksi Tidak tersedia',
        ]);
    }

    public function cek_transaksi(Collection $daftarTransaksi): bool
    {
        return $daftarTransaksi->isNotEmpty();
    }

    // PC-34
    public function kirim_laporan_transaksi(Request $request)
    {
        $periode = $this->validasiPeriode($request);
        $daftarTransaksi = $this->laporanEntity->get_data_transaksi($periode['jenis'], $periode['periode_awal'], $periode['periode_akhir']);
        $kembali = redirect()->route('staff.laporan', $periode);

        if (! $this->cek_transaksi($daftarTransaksi)) {
            return $kembali->with('error', 'Laporan Transaksi Tidak tersedia');
        }

        // statusKirim = CALL Laporan_Entity.kirim_data_laporan_transaksi()
        $statusKirim = $this->laporanEntity->kirim_data_laporan_transaksi($daftarTransaksi, $periode);

        // CALL J1_Halaman_laporan.tampilkan_pesan(...)
        return $statusKirim
            ? $kembali->with('success', 'Laporan Transaksi Berhasil Dikirim')
            : $kembali->with('error', 'Laporan Transaksi Gagal Dikirim');
    }

    // PC-45
    public function ekspor_laporan(Request $request)
    {
        $periode = $this->validasiPeriode($request);
        $formatFile = $request->validate(['format_file' => ['required', 'in:pdf,xlsx']])['format_file'];
        $kembali = redirect()->route('manager.laporan', $periode);

        // daftarTransaksi = CALL Laporan_Entity.get_data_transaksi(jenis, periodeAwal, periodeAkhir)
        $daftarTransaksi = $this->laporanEntity->get_data_transaksi($periode['jenis'], $periode['periode_awal'], $periode['periode_akhir']);
        if (! $this->cek_transaksi($daftarTransaksi)) {
            return $kembali->with('error', 'Laporan Transaksi Tidak tersedia');
        }

        // statusEkspor = CALL Laporan_Kontrol.proses_ekspor_file(daftarTransaksi, formatFile)
        $file = $this->proses_ekspor_file($daftarTransaksi, $formatFile, $periode);

        return $file ?? $kembali->with('error', 'Laporan gagal diekspor');
    }

    public function proses_ekspor_file(Collection $daftarTransaksi, string $formatFile, array $periode): ?Response
    {
        $namaFile = sprintf('laporan-transaksi_%s_%s.%s', $periode['periode_awal'], $periode['periode_akhir'], $formatFile);
        $ringkasan = Laporan_Entity::ringkasan($daftarTransaksi);

        try {
            if ($formatFile === 'pdf') {
                return Pdf::loadView('reports.pdf', compact('daftarTransaksi', 'periode', 'ringkasan'))
                    ->setPaper('a4', 'landscape')
                    ->download($namaFile);
            }

            return $this->excel($daftarTransaksi, $periode, $ringkasan, $namaFile);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private function excel(Collection $daftarTransaksi, array $periode, array $ringkasan, string $namaFile): Response
    {
        $sheet = (new Spreadsheet)->getActiveSheet()->setTitle('Laporan Transaksi');
        $sheet->setCellValue('A1', 'Laporan Transaksi Travel Tour');
        $sheet->setCellValue('A2', 'Periode '.$periode['jenis'].': '.tanggal($periode['periode_awal']).' - '.tanggal($periode['periode_akhir']));
        $sheet->setCellValue('A3', 'Jumlah Pemesanan Lunas: '.$ringkasan['jumlah_lunas'].' | Total Peserta: '.$ringkasan['total_peserta'].' | Total Transaksi: '.rupiah($ringkasan['total_transaksi']));
        $sheet->fromArray(['Tanggal', 'Kode Pemesanan', 'Customer', 'Paket Wisata', 'Jumlah Peserta', 'Metode Pembayaran', 'Total Bayar', 'Status Pembayaran'], null, 'A5');

        $baris = 6;
        foreach ($daftarTransaksi as $t) {
            $sheet->fromArray([tanggal($t->tanggal_bayar), $t->kode_pemesanan, $t->nama_customer, $t->nama_paket, (int) $t->jumlah_peserta, $t->metode_pembayaran ?? 'Transfer Bank', (float) $t->nominal, $t->status_pembayaran], null, 'A'.$baris++);
        }
        $sheet->getStyle('G6:G'.$baris)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        foreach (range('A', 'H') as $kolom) {
            $sheet->getColumnDimension($kolom)->setAutoSize(true);
        }

        $writer = new Xlsx($sheet->getParent());

        return response()->streamDownload(fn () => $writer->save('php://output'), $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
