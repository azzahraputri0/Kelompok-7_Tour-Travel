<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_empty_reports_use_today_and_zero_summary(): void
    {
        DB::table('PEMBAYARAN')->delete();
        foreach (['Staff' => '/staff/laporan', 'Manager' => '/manager/laporan'] as $role => $url) {
            $this->actingAs(Umum::where('role', $role)->first())->get($url)->assertOk()
                ->assertViewHas('periode', fn ($p) => $p['periode_awal'] === today()->toDateString())
                ->assertViewHas('daftarTransaksi', fn ($rows) => $rows->isEmpty())
                ->assertViewHas('ringkasan', fn ($s) => $s['total_transaksi'] === 0.0);
        }
    }

    public function test_explicit_report_filters_are_respected_and_invalid_dates_rejected(): void
    {
        $this->actingAs(Umum::where('role', 'Manager')->first());
        $this->get('/manager/laporan?jenis=Harian&periode_awal=2000-01-01&periode_akhir=2000-01-01')
            ->assertOk()->assertViewHas('daftarTransaksi', fn ($rows) => $rows->isEmpty());
        $this->get('/manager/laporan?jenis=Harian&periode_awal=bad&periode_akhir=2000-01-01')
            ->assertSessionHasErrors('periode_awal');
        $this->get('/manager/laporan?jenis=Harian&periode_awal=2026-01-02&periode_akhir=2026-01-01')
            ->assertSessionHasErrors('periode_akhir');
    }

    public function test_vehicle_photo_upload_is_persisted_retained_and_validated(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = Umum::where('role', 'Customer')->first();
        $admin->update(['role' => 'Admin']);
        $this->actingAs($admin);
        $data = ['plat_nomor' => 'B 123 QA', 'jenis_kendaraan' => 'Bus', 'merek' => 'Test', 'kapasitas' => 20, 'tahun_produksi' => 2024, 'status' => 'Tersedia'];
        $this->post('/admin/kendaraan', $data + ['foto_kendaraan' => \Illuminate\Http\UploadedFile::fake()->image('vehicle.png')])->assertSessionHasNoErrors();
        $vehicle = DB::table('KENDARAAN')->where('plat_nomor', 'B 123 QA')->first();
        $this->assertNotNull($vehicle->foto_kendaraan);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($vehicle->foto_kendaraan);
        $this->put('/admin/kendaraan/'.$vehicle->id_kendaraan, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('KENDARAAN', ['id_kendaraan' => $vehicle->id_kendaraan, 'foto_kendaraan' => $vehicle->foto_kendaraan]);
        $this->put('/admin/kendaraan/'.$vehicle->id_kendaraan, $data + ['foto_kendaraan' => \Illuminate\Http\UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf')])->assertSessionHasErrors('foto_kendaraan');
        $this->put('/admin/kendaraan/'.$vehicle->id_kendaraan, $data + ['foto_kendaraan' => \Illuminate\Http\UploadedFile::fake()->image('large.png')->size(2049)])->assertSessionHasErrors('foto_kendaraan');
        $this->get('/admin/kendaraan/'.$vehicle->id_kendaraan.'/edit')->assertOk()->assertSee('foto_kendaraan');
    }

    public function test_driver_photo_is_saved_and_invalid_upload_rejected(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = Umum::where('role', 'Customer')->first();
        $admin->update(['role' => 'Admin']);
        $this->actingAs($admin);
        $data = ['nama_lengkap' => 'Photo Driver', 'nomor_telepon' => '0812345678', 'nomor_sim' => 'PHOTOQA', 'alamat' => 'Street', 'status' => 'Tersedia'];
        $this->post('/admin/driver', $data + ['foto_driver' => \Illuminate\Http\UploadedFile::fake()->image('driver.png')])->assertSessionHasNoErrors();
        $driver = DB::table('DRIVER')->where('nomor_sim', 'PHOTOQA')->first();
        $this->assertNotEmpty($driver->foto_driver ?? null);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($driver->foto_driver);
        $this->put('/admin/driver/'.$driver->id_driver, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('DRIVER', ['id_driver' => $driver->id_driver, 'foto_driver' => $driver->foto_driver]);
        $this->put('/admin/driver/'.$driver->id_driver, $data + ['foto_driver' => \Illuminate\Http\UploadedFile::fake()->create('bad.pdf', 10, 'application/pdf')])->assertSessionHasErrors('foto_driver');
        $this->get('/admin/driver/'.$driver->id_driver.'/edit')->assertOk()->assertSee('foto_driver');
    }

    public function test_reports_default_to_latest_transaction_day(): void
    {
        $latest = DB::table('PEMBAYARAN')->max('tanggal_bayar');
        foreach (['Staff' => '/staff/laporan', 'Manager' => '/manager/laporan'] as $role => $url) {
            $this->actingAs(Umum::where('role', $role)->first())->get($url)
                ->assertOk()
                ->assertViewHas('periode', fn ($period) => $period['periode_awal'] === $latest && $period['periode_akhir'] === $latest)
                ->assertViewHas('daftarTransaksi', fn ($rows) => $rows !== null && $rows->isNotEmpty() && $rows->every(fn ($row) => $row->tanggal_bayar === $latest));
        }
    }
}
