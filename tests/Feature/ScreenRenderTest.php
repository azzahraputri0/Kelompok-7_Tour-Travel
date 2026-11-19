<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScreenRenderTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    public function test_all_get_screens_render_real_blade_for_each_role(): void
    {
        $this->seed(\Database\Seeders\DemoAdminSeeder::class);
        $package = DB::table('PAKET_TRAVEL')->first();
        $customer = Umum::where('role', 'Customer')->first();
        $order = DB::table('PEMESANAN')->where('FK_id_pengguna', $customer->id_pengguna)->first();
        $payment = DB::table('PEMBAYARAN')->where('id_pemesanan', $order->id_pemesanan)->first();
        $routes = [
            'Guest' => ['/', '/paket', '/paket/'.$package->id_paket, '/login', '/register'],
            'Customer' => ['/customer/pesanan', '/customer/pemesanan/'.$package->id_paket.'/buat', '/customer/pesanan/'.$order->id_pemesanan, '/customer/pesanan/'.$order->id_pemesanan.'/bayar'],
            'Staff' => ['/staff/dashboard', '/staff/laporan', '/staff/pemesanan', '/staff/pemesanan/'.$order->id_pemesanan, '/staff/pembayaran', '/staff/pembayaran/'.$payment->id_pembayaran, '/staff/penugasan', '/pelanggan', '/pelanggan/cari?keyword=Siti', '/pelanggan/'.$customer->id_pengguna],
            'Manager' => ['/manager/dashboard', '/manager/laporan', '/pelanggan', '/pelanggan/cari?keyword=Siti', '/pelanggan/'.$customer->id_pengguna],
            'Driver' => ['/driver/dashboard', '/driver/jadwal-tour', '/driver/jadwal-tour/JDW-2026-001'],
            'Admin' => ['/admin/dashboard'],
        ];
        foreach (['paket' => ['PAKET_TRAVEL', 'id_paket'], 'kendaraan' => ['KENDARAAN', 'id_kendaraan'], 'driver' => ['DRIVER', 'id_driver']] as $resource => [$table, $key]) {
            foreach (['', '/create', '/'.DB::table($table)->first()->{$key}.'/edit'] as $suffix) {
                $routes['Admin'][] = '/admin/'.$resource.$suffix;
            }
        }
        foreach ($routes as $role => $urls) {
            if ($role !== 'Guest') {
                $this->actingAs(Umum::where('role', $role)->firstOrFail());
            }
            foreach ($urls as $url) {
                $response = $this->get($url);
                $this->assertSame(200, $response->status(), $role.' '.$url.' '.$response->exception?->getMessage());
                $response->assertSee('<html', false)->assertSee('</html>', false);
            }
        }
        $this->actingAs($customer)->get('/customer/dashboard')->assertRedirect('/customer/pesanan');
        $this->get('/customer/pemesanan/'.$package->id_paket.'/buat')->assertSee('value="'.$customer->nama_lengkap.'"', false);
        $this->post('/customer/pemesanan/'.$package->id_paket.'/penumpang', ['jadwal_keberangkatan' => $package->jadwal_keberangkatan, 'jumlah_peserta' => 1])->assertOk()->assertSee('<html', false);
    }
}
