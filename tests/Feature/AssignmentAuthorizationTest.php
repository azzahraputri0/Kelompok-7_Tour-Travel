<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssignmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DemoAdminSeeder::class);
    }

    private function payload(): array
    {
        $order = DB::table('PEMESANAN')->first();
        DB::table('JADWAL_PERJALANAN')->delete();
        DB::table('PEMESANAN')->where('id_pemesanan', $order->id_pemesanan)->update([
            'status' => 'Terkonfirmasi',
            'jadwal_keberangkatan' => today()->addMonths(3)->toDateString(),
            'jumlah_peserta' => 1,
        ]);

        return ['id_pemesanan' => $order->id_pemesanan, 'id_driver' => 'DRV-002', 'id_kendaraan' => 'KND-003'];
    }

    private function eligibleAssignment(array $payload): string
    {
        DB::table('JADWAL_PERJALANAN')->insert($payload + [
            'id_jadwal' => 'AUTH-TEST',
            'tanggal_berangkat' => today()->addMonths(3)->toDateString(),
            'status' => 'Terjadwal',
        ]);

        return 'AUTH-TEST';
    }

    private function snapshot(): array
    {
        return collect(['JADWAL_PERJALANAN', 'PEMESANAN', 'DRIVER', 'KENDARAAN', 'UMUM'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->get()->toJson()])->all();
    }

    public function test_admin_can_list_create_and_delete_with_admin_navigation_and_form_actions(): void
    {
        $payload = $this->payload();
        $this->actingAs(Umum::where('role', 'Admin')->firstOrFail());
        $this->get('/admin/penugasan')->assertOk()->assertViewIs('admin.penugasan.index')
            ->assertSee('href="'.url('/admin/penugasan').'" class="aktif"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('action="'.url('/admin/penugasan').'"', false);
        $this->post('/admin/penugasan', $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('JADWAL_PERJALANAN', $payload);
        $id = DB::table('JADWAL_PERJALANAN')->value('id_jadwal');
        $this->get('/admin/penugasan')->assertOk()
            ->assertSee('action="'.url('/admin/penugasan/'.$id).'"', false)
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('name="_token"', false)->assertDontSee('/staff/penugasan');
        $this->delete('/admin/penugasan/'.$id)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('JADWAL_PERJALANAN', ['id_jadwal' => $id]);
    }

    public static function forbiddenRoles(): array
    {
        return array_map(fn ($role) => [$role], ['Staff', 'Customer', 'Manager', 'Driver']);
    }

    #[DataProvider('forbiddenRoles')]
    public function test_other_roles_cannot_access_any_admin_assignment_action(string $role): void
    {
        $payload = $this->payload();
        $this->actingAs(Umum::where('role', $role)->firstOrFail());
        $before = $this->snapshot();
        $this->get('/admin/penugasan')->assertForbidden();
        $this->assertSame($before, $this->snapshot());
        $this->post('/admin/penugasan', $payload)->assertForbidden();
        $this->assertSame($before, $this->snapshot());
        $id = $this->eligibleAssignment($payload);
        $before = $this->snapshot();
        $this->delete('/admin/penugasan/'.$id)->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_legacy_staff_actions_are_removed(): void
    {
        $payload = $this->payload();
        $id = $this->eligibleAssignment($payload);
        $before = $this->snapshot();
        foreach (['Staff', 'Admin'] as $role) {
            $this->actingAs(Umum::where('role', $role)->firstOrFail());
            $this->get('/staff/penugasan')->assertNotFound();
            $this->post('/staff/penugasan', $payload)->assertNotFound();
            $this->delete('/staff/penugasan/'.$id)->assertNotFound();
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_guests_are_redirected_to_login_for_all_admin_actions(): void
    {
        $payload = $this->payload();
        $id = $this->eligibleAssignment($payload);
        $before = $this->snapshot();
        $this->get('/admin/penugasan')->assertRedirect('/login');
        $this->post('/admin/penugasan', $payload)->assertRedirect('/login');
        $this->delete('/admin/penugasan/'.$id)->assertRedirect('/login');
        $this->assertSame($before, $this->snapshot());
    }

    public function test_staff_navigation_keeps_transactions_but_has_no_assignments(): void
    {
        $this->actingAs(Umum::where('role', 'Staff')->firstOrFail());
        $this->get('/staff/dashboard')->assertOk()->assertDontSee('Penugasan')
            ->assertDontSee('/staff/penugasan')->assertDontSee('/admin/penugasan')
            ->assertSee(url('/staff/pemesanan'))->assertSee(url('/staff/pembayaran'))
            ->assertSee(url('/staff/laporan'));
    }

    public function test_admin_cannot_delete_started_or_non_scheduled_assignments(): void
    {
        $payload = $this->payload();
        $id = $this->eligibleAssignment($payload);
        $this->actingAs(Umum::where('role', 'Admin')->firstOrFail());
        foreach ([['Terjadwal', today()->toDateString()], ['Selesai', today()->addMonths(3)->toDateString()]] as [$status, $date]) {
            DB::table('JADWAL_PERJALANAN')->where('id_jadwal', $id)->update(['status' => $status, 'tanggal_berangkat' => $date]);
            $before = $this->snapshot();
            $this->delete('/admin/penugasan/'.$id)->assertSessionHasErrors('status');
            $this->assertSame($before, $this->snapshot());
        }
    }
}
