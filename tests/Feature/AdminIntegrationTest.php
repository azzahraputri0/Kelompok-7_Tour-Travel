<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminIntegrationTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    public function test_demo_admin_seeder_is_explicit_idempotent_and_local_only(): void
    {
        $this->assertDatabaseMissing('UMUM', ['role' => 'Admin']);
        $this->seed(\Database\Seeders\DemoAdminSeeder::class);
        $admin = Umum::where('email', 'admin@traveltour.test')->firstOrFail();
        $this->assertSame('Admin', $admin->role);
        $this->assertTrue(password_verify('password123', $admin->password));
        $admin->update(['password' => bcrypt('changed-password')]);
        $this->seed(\Database\Seeders\DemoAdminSeeder::class);
        $this->assertTrue(password_verify('changed-password', $admin->fresh()->password));
        $admin->delete();
        $this->app->instance('env', 'production');
        try {
            (new \Database\Seeders\DemoAdminSeeder)->run();
            $this->fail('Production must refuse demo credentials.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('local', $e->getMessage());
        }
        $this->assertDatabaseMissing('UMUM', ['role' => 'Admin']);
    }

    public function test_package_dates_are_strict_and_normalized_on_create_and_update(): void
    {
        $this->travelTo(now()->setDate(2026, 1, 1));
        $admin = Umum::where('role', 'Customer')->first();
        $admin->update(['role' => 'Admin']);
        $this->actingAs($admin);
        $data = ['nama_paket' => 'Date test', 'tujuan' => 'Bali', 'durasi' => '2 hari', 'harga' => 100000, 'kuota' => 10];
        $this->post('/admin/paket', $data + ['jadwal_keberangkatan' => '28/02/2028'])->assertSessionHasNoErrors();
        $package = DB::table('PAKET_TRAVEL')->where('nama_paket', 'Date test')->first();
        $this->assertNotNull($package);
        $this->assertSame('2028-02-28', $package->jadwal_keberangkatan);
        foreach (['29/02/2028' => '2028-02-29', '2028-03-01' => '2028-03-01'] as $input => $expected) {
            $this->put('/admin/paket/'.$package->id_paket, $data + ['jadwal_keberangkatan' => $input])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('PAKET_TRAVEL', ['id_paket' => $package->id_paket, 'jadwal_keberangkatan' => $expected]);
        }
        foreach (['31/02/2028', '29/02/2027', '01/13/2028', '2028-02-30', '1/2/2028', '02-03-2028', 'tomorrow', '2028-3-1', '31/12/2025'] as $input) {
            $this->put('/admin/paket/'.$package->id_paket, $data + ['jadwal_keberangkatan' => $input])->assertSessionHasErrors('jadwal_keberangkatan');
        }
        $this->assertDatabaseHas('PAKET_TRAVEL', ['id_paket' => $package->id_paket, 'jadwal_keberangkatan' => '2028-03-01']);
    }
}
