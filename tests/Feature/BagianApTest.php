<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BagianApTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function akun(string $email): Umum
    {
        return Umum::where('email', $email)->firstOrFail();
    }

    private function periode(): array
    {
        return ['jenis' => 'Bulanan', 'periode_awal' => Carbon::today()->subMonths(7)->toDateString(), 'periode_akhir' => Carbon::today()->toDateString()];
    }

    private function periodeKosong(): array
    {
        return ['jenis' => 'Harian', 'periode_awal' => '2020-01-01', 'periode_akhir' => '2020-01-02'];
    }

    public function test_login_tiap_role_diarahkan_ke_dashboard(): void
    {
        foreach (['staff' => '/staff/dashboard', 'manager' => '/manager/dashboard', 'driver' => '/driver/dashboard'] as $nama => $tujuan) {
            $this->post('/login', ['email' => "$nama@traveltour.test", 'password' => 'password123'])->assertRedirect($tujuan);
            $this->get($tujuan)->assertOk();
            $this->post('/logout')->assertRedirect('/login');
            $this->assertGuest();
        }
    }

    public function test_login_gagal_dan_customer_ditolak(): void
    {
        $this->post('/login', ['email' => 'staff@traveltour.test', 'password' => 'salah'])->assertSessionHasErrors(['email' => 'Email atau password salah']);
        $this->post('/login', ['email' => 'siti.rahmawati@mail.test', 'password' => 'password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get('/login')->assertOk()->assertSee('Akun Demo');
    }

    public function test_role_salah_ditolak(): void
    {
        $this->actingAs($this->akun('driver@traveltour.test'))->get('/staff/laporan')->assertForbidden();
        $this->actingAs($this->akun('driver@traveltour.test'))->get('/pelanggan')->assertForbidden();
        $this->actingAs($this->akun('staff@traveltour.test'))->get('/manager/laporan')->assertForbidden();
        $this->actingAs($this->akun('manager@traveltour.test'))->post('/staff/laporan/kirim', $this->periode())->assertForbidden();
        auth()->logout();
        $this->get('/staff/dashboard')->assertRedirect('/login');
    }

    public function test_uc10_staff_melihat_laporan(): void
    {
        $staff = $this->akun('staff@traveltour.test');
        $this->actingAs($staff)->get('/staff/laporan')->assertOk()->assertSee('Tampilkan');
        $this->actingAs($staff)->get('/staff/laporan?'.http_build_query($this->periode()))
            ->assertOk()->assertSee('Kode')->assertSee('Tanggal')->assertDontSee('Status')->assertSee('Kirim Laporan')->assertSee('Rp. ');
        $this->actingAs($staff)->get('/staff/laporan?'.http_build_query($this->periodeKosong()))
            ->assertOk()->assertSee('Laporan Transaksi Tidak tersedia')->assertDontSee('Kirim Laporan');
        $this->actingAs($staff)->get('/staff/laporan?jenis=Harian&periode_awal=2026-02-10&periode_akhir=2026-02-01')
            ->assertSessionHasErrors('periode_akhir');
    }

    public function test_uc11_kirim_laporan_berhasil_dan_gagal(): void
    {
        Mail::fake();
        $staff = $this->akun('staff@traveltour.test');
        $this->actingAs($staff)->post('/staff/laporan/kirim', $this->periode())->assertSessionHas('success', 'Laporan Transaksi Berhasil Dikirim');
        Mail::assertSent(\App\Mail\LaporanTransaksiMail::class, fn ($m) => $m->hasTo('manager@traveltour.test'));

        $this->actingAs($staff)->post('/staff/laporan/kirim', $this->periodeKosong())->assertSessionHas('error', 'Laporan Transaksi Tidak tersedia');

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP mati'));
        $this->actingAs($staff)->post('/staff/laporan/kirim', $this->periode())->assertSessionHas('error', 'Laporan Transaksi Gagal Dikirim');
    }

    public function test_uc12_pelanggan_daftar_cari_detail(): void
    {
        foreach (['staff@traveltour.test', 'manager@traveltour.test'] as $email) {
            $u = $this->akun($email);
            $this->actingAs($u)->get('/pelanggan')->assertOk()->assertSee('Siti Rahmawati')->assertSee('Terverifikasi')->assertSee('Belum Lengkap')->assertDontSee('Agus Pratama');
            $this->actingAs($u)->get('/pelanggan/cari?keyword=Dewi')->assertOk()->assertSee('Dewi Lestari')->assertDontSee('Siti Rahmawati');
            $this->actingAs($u)->get('/pelanggan/cari?keyword=081311220009')->assertOk()->assertSee('Rizky Ramadhan');
            $this->actingAs($u)->get('/pelanggan/cari?keyword=zzzz')->assertOk()->assertSee('Data Pelanggan Tidak Ditemukan');
            $this->actingAs($u)->get('/pelanggan/USR-2026-005')->assertOk()->assertSee('3171014501900005')->assertSee('Riwayat Pemesanan');
            $this->actingAs($u)->get('/pelanggan/USR-2026-001')->assertNotFound();
        }
    }

    public function test_uc16_driver_hanya_melihat_jadwal_sendiri(): void
    {
        $driver = $this->akun('driver@traveltour.test');
        $this->actingAs($driver)->get('/driver/jadwal-tour')->assertOk()->assertSee('JDW-2026-001', false)->assertSee('Detail');
        $this->actingAs($driver)->get('/driver/jadwal-tour/JDW-2026-001')->assertOk()->assertSee('Plat Nomor')->assertSee('Jumlah Peserta');
        $this->actingAs($driver)->get('/driver/dashboard')->assertOk()->assertSee('Halo, Budi Santoso');

        $driver2 = $this->akun('driver2@traveltour.test');
        $this->actingAs($driver2)->get('/driver/jadwal-tour')->assertOk()->assertSee('Jadwal Tour tidak tersedia');
        $this->actingAs($driver2)->get('/driver/jadwal-tour/JDW-2026-001')->assertNotFound();
    }

    public function test_uc17_manager_meninjau_laporan(): void
    {
        $m = $this->akun('manager@traveltour.test');
        $this->actingAs($m)->get('/manager/laporan?'.http_build_query($this->periode()))->assertOk()
            ->assertSee('Jumlah Pemesanan Lunas')->assertSee('Total Peserta')->assertSee('Total Transaksi Periode')
            ->assertSee('Metode')->assertSee('Tanggal')->assertDontSee('Status')->assertSee('Ekspor PDF')->assertSee('Ekspor Excel');
        $this->actingAs($m)->get('/manager/laporan?'.http_build_query($this->periodeKosong()))->assertOk()
            ->assertSee('Laporan Transaksi Tidak tersedia')->assertDontSee('Ekspor PDF');
        $this->actingAs($m)->get('/manager/dashboard')->assertOk()->assertSee('Pendapatan')->assertSee('Total Driver');
    }

    public function test_uc18_ekspor_pdf_dan_excel(): void
    {
        $m = $this->akun('manager@traveltour.test');
        $pdf = $this->actingAs($m)->get('/manager/laporan/ekspor?'.http_build_query($this->periode() + ['format_file' => 'pdf']));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $xlsx = $this->actingAs($m)->get('/manager/laporan/ekspor?'.http_build_query($this->periode() + ['format_file' => 'xlsx']));
        $xlsx->assertOk();
        $this->assertStringStartsWith('PK', $xlsx->streamedContent());

        $this->actingAs($m)->get('/manager/laporan/ekspor?'.http_build_query($this->periodeKosong() + ['format_file' => 'pdf']))
            ->assertSessionHas('error', 'Laporan Transaksi Tidak tersedia');
        $this->actingAs($m)->get('/manager/laporan/ekspor?'.http_build_query($this->periode() + ['format_file' => 'exe']))
            ->assertSessionHasErrors('format_file');
    }
}
