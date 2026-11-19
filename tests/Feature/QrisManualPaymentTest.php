<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisManualPaymentTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    private function booking(): object
    {
        $u = Umum::where('role', 'Customer')->first();
        $p = DB::table('PAKET_TRAVEL')->first();
        $this->actingAs($u)->post('/customer/pemesanan/'.$p->id_paket, [
            'jadwal_keberangkatan' => $p->jadwal_keberangkatan, 'jumlah_peserta' => 1,
            'email' => $u->email, 'no_hp' => '0812345678',
            'penumpang' => [['nama_lengkap' => 'QRIS Test', 'nik' => '1234567890123456', 'tanggal_lahir' => '2000-01-01']],
        ])->assertSessionHasNoErrors();
        return DB::table('PEMESANAN')->where('email', $u->email)->first();
    }

    public function test_missing_or_unsafe_qr_shows_demo_without_payment_image(): void
    {
        $o = $this->booking();
        foreach ([null, 'https://third-party.test/qr.png', '../secret.png', '//evil.test/qr.png', 'storage/missing.png'] as $image) {
            config(['travel.qris_image' => $image]);
            $this->get('/customer/pesanan/'.$o->id_pemesanan.'/bayar')->assertOk()
                ->assertSee('QRIS Manual')->assertSee('DEMO')->assertSee('Jangan melakukan pembayaran')
                ->assertSee('bukti_pembayaran')->assertDontSee('alt="QRIS resmi pengelola"', false);
        }
    }

    public function test_configured_local_image_has_manual_instructions_not_settlement(): void
    {
        $o = $this->booking();
        $path = public_path('qris-test-fixture.png');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+afoUAAAAASUVORK5CYII='));
        try {
            config(['travel.qris_image' => '/qris-test-fixture.png']);
            $this->get('/customer/pesanan/'.$o->id_pemesanan.'/bayar')->assertOk()
                ->assertSee('alt="QRIS resmi pengelola"', false)->assertSee('/qris-test-fixture.png')
                ->assertSee('QR statis')->assertSee('verifikasi manual')->assertDontSee('DEMO —');
            $this->assertDatabaseMissing('PEMBAYARAN', ['id_pemesanan' => $o->id_pemesanan]);
            $this->assertDatabaseHas('PEMESANAN', ['id_pemesanan' => $o->id_pemesanan, 'status' => 'Belum Terbayar']);
        } finally { unlink($path); }
    }

    public function test_new_and_retried_proof_use_qris_without_rewriting_history(): void
    {
        Storage::fake('local');
        $historical = DB::table('PEMBAYARAN')->first();
        DB::table('PEMBAYARAN')->where('id_pembayaran', $historical->id_pembayaran)->update(['metode_pembayaran' => 'Transfer Bank']);
        $o = $this->booking();
        foreach ([false, true] as $retry) {
            if ($retry) {
                DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->update(['status' => 'Ditolak', 'metode_pembayaran' => 'Transfer Bank']);
                DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['status' => 'Belum Terbayar']);
            }
            $this->post('/customer/pesanan/'.$o->id_pemesanan.'/bayar', ['bukti_pembayaran' => UploadedFile::fake()->create('demo.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('PEMBAYARAN', ['id_pemesanan' => $o->id_pemesanan, 'metode_pembayaran' => 'QRIS', 'status' => 'Proses Verifikasi']);
            $this->assertDatabaseHas('PEMBAYARAN', ['id_pembayaran' => $historical->id_pembayaran, 'metode_pembayaran' => 'Transfer Bank']);
        }

        $query = http_build_query(['jenis' => 'Bulanan', 'periode_awal' => '2000-01-01', 'periode_akhir' => today()->addYears(10)->toDateString()]);
        foreach (['Staff' => '/staff/laporan', 'Manager' => '/manager/laporan'] as $role => $url) {
            $this->actingAs(Umum::where('role', $role)->first())->get($url.'?'.$query)
                ->assertOk()->assertSee('Transfer Bank')->assertSee('QRIS')
                ->assertViewHas('daftarTransaksi', fn ($rows) => $rows->firstWhere('id_pembayaran', $historical->id_pembayaran)->metode_pembayaran === 'Transfer Bank');
        }
        $pdf = $this->get('/manager/laporan/ekspor?'.$query.'&format_file=pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
        $pdfPath = tempnam(sys_get_temp_dir(), 'qris-pdf-');
        $xlsxPath = tempnam(sys_get_temp_dir(), 'qris-xlsx-');
        try {
            file_put_contents($pdfPath, $pdf->getContent());
            $process = new \Symfony\Component\Process\Process(['pdftotext', $pdfPath, '-']);
            $process->mustRun();
            $this->assertStringContainsString('Transfer Bank', $process->getOutput());
            $this->assertStringContainsString('QRIS', $process->getOutput());
            $xlsx = $this->get('/manager/laporan/ekspor?'.$query.'&format_file=xlsx')->assertOk();
            file_put_contents($xlsxPath, $xlsx->streamedContent());
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsxPath);
            $methods = array_column($book->getActiveSheet()->toArray(), 5);
            $this->assertContains('Transfer Bank', $methods);
            $this->assertContains('QRIS', $methods);
            $book->disconnectWorksheets();
        } finally {
            unlink($pdfPath);
            unlink($xlsxPath);
        }
        $this->assertDatabaseHas('PEMBAYARAN', ['id_pembayaran' => $historical->id_pembayaran, 'metode_pembayaran' => 'Transfer Bank']);
    }
}
