<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MasterContractsTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    public function test_routes_use_source_controllers_and_methods(): void
    {
        foreach (['kendaraan' => 'Kendaraan', 'driver' => 'Driver', 'paket' => 'PaketTravel'] as $resource => $class) {
            $controller = 'App\\Http\\Controllers\\'.$class.'_Kontrol';
            $entity = 'App\\Entities\\'.$class.'_Entity';
            $this->assertTrue(class_exists($entity), $entity);
            $methods = $resource === 'paket'
                ? ['index' => 'getDaftarPaketTravel', 'edit' => 'getPaketTravel', 'store' => 'validasiPaket', 'update' => 'validasiPaket', 'destroy' => 'hapusPaket']
                : ['index' => 'getDaftar'.$class, 'edit' => 'get'.$class, 'store' => 'validasiTambah'.$class, 'update' => 'validasiEdit'.$class, 'destroy' => 'hapus'.$class];
            foreach ($methods as $route => $method) {
                $this->assertSame($controller.'@'.$method, Route::getRoutes()->getByName('admin.'.$resource.'.'.$route)->getActionName());
            }
            foreach (['getAllDataMaster', 'getAllData'.$class, 'getData'.$class, 'simpan', $resource === 'paket' ? 'hapusDataPkaketTravel' : 'hapusData'.$class] as $method) {
                $this->assertTrue(method_exists($entity, $method), $entity.'::'.$method);
            }
            $this->assertTrue(method_exists($controller, 'getDataMaster'));
        }
    }

    public function test_route_validation_calls_entity_persistence_and_blocks_invalid_data(): void
    {
        $admin = Umum::where('role', 'Staff')->first();
        $admin->update(['role' => 'Admin']);
        $entity = $this->partialMock(\App\Entities\Kendaraan_Entity::class);
        $entity->shouldReceive('simpan')->once()->withArgs(fn ($data, $id) => $id === null && $data['plat_nomor'] === 'B CONTRACT')->passthru();
        $data = ['plat_nomor' => 'B CONTRACT', 'jenis_kendaraan' => 'Bus', 'merek' => 'Toyota', 'kapasitas' => 12, 'tahun_produksi' => 2024, 'status' => 'Tersedia'];
        $this->actingAs($admin)->post('/admin/kendaraan', array_replace($data, ['kapasitas' => 0]))->assertSessionHasErrors('kapasitas');
        $this->assertDatabaseMissing('KENDARAAN', ['plat_nomor' => 'B CONTRACT']);
        $this->post('/admin/kendaraan', $data)->assertRedirect(route('admin.kendaraan.index'));
        $this->assertDatabaseHas('KENDARAAN', $data);
        $id = DB::table('KENDARAAN')->where('plat_nomor', 'B CONTRACT')->value('id_kendaraan');
        $this->assertSame('B CONTRACT', $entity->getDataKendaraan($id)->plat_nomor);
        $this->assertTrue($entity->hapusDataKendaraan($id));
        $this->assertDatabaseMissing('KENDARAAN', ['id_kendaraan' => $id]);
    }

    public function test_assignment_rejects_unlinked_and_wrong_role_accounts_and_filters_options(): void
    {
        $staff = Umum::where('role', 'Staff')->first();
        $order = DB::table('PEMESANAN')->first();
        DB::table('JADWAL_PERJALANAN')->where('id_pemesanan', $order->id_pemesanan)->delete();
        DB::table('PEMESANAN')->where('id_pemesanan', $order->id_pemesanan)->update(['status' => 'Terkonfirmasi', 'jadwal_keberangkatan' => today()->addMonths(3)->toDateString(), 'jumlah_peserta' => 1]);
        foreach ([null, $staff->id_pengguna] as $account) {
            DB::table('DRIVER')->where('id_driver', 'DRV-002')->update(['FK_id_pengguna' => $account]);
            $this->actingAs($staff)->get('/staff/penugasan')->assertOk()->assertViewHas('daftarDriver', fn ($drivers) => !$drivers->contains('id_driver', 'DRV-002'));
            $this->post('/staff/penugasan', ['id_pemesanan' => $order->id_pemesanan, 'id_driver' => 'DRV-002', 'id_kendaraan' => 'KND-003'])->assertSessionHasErrors('id_driver');
            $this->assertDatabaseMissing('JADWAL_PERJALANAN', ['id_pemesanan' => $order->id_pemesanan]);
        }
    }
}
