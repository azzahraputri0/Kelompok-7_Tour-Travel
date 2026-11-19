<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TravelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function customer()
    {
        return Umum::where('role', 'Customer')->first();
    }

    public function test_registration_forces_customer_and_hashes_password()
    {
        $this->post('/register', ['nama_lengkap' => 'New Customer', 'email' => 'new@example.test', 'nomor_telepon' => '0812345678', 'password' => 'secure123', 'password_confirmation' => 'secure123', 'role' => 'Admin'])->assertRedirect('/login');
        $u = Umum::where('email', 'new@example.test')->firstOrFail();
        $this->assertSame('Customer', $u->role);
        $this->assertTrue(password_verify('secure123', $u->password));
    }

    public function test_customer_authentication_and_ap_boundary()
    {
        $this->post('/login', ['email' => $this->customer()->email, 'password' => 'password123'])->assertRedirect('/customer/dashboard');
        $this->get('/staff/laporan')->assertForbidden();
    }

    public function test_booking_payment_verification_and_assignment()
    {
        Storage::fake('local');
        $u = $this->customer();
        $p = DB::table('PAKET_TRAVEL')->first();
        $data = ['jadwal_keberangkatan' => $p->jadwal_keberangkatan, 'jumlah_peserta' => 1, 'email' => $u->email, 'no_hp' => '0812345678', 'penumpang' => [['nama_lengkap' => 'Passenger', 'nik' => '1234567890123456', 'tanggal_lahir' => '2000-01-01']]];
        $this->actingAs($u)->post('/customer/pemesanan/'.$p->id_paket, $data)->assertSessionHasNoErrors()->assertRedirect();
        $o = DB::table('PEMESANAN')->where('email', $u->email)->first();
        $this->assertSame('Belum Terbayar', $o->status);
        $this->assertEquals($p->harga, $o->total_pembayaran);
        $this->post('/customer/pesanan/'.$o->id_pemesanan.'/bayar', ['bukti_pembayaran' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf')])->assertSessionHasNoErrors();
        $pay = DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->first();
        $this->assertSame('Proses Verifikasi', $pay->status);
        $this->actingAs(Umum::where('role', 'Staff')->first())->post('/staff/pembayaran/'.$pay->id_pembayaran.'/verifikasi')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('PEMESANAN', ['id_pemesanan' => $o->id_pemesanan, 'status' => 'Terkonfirmasi']);
        $this->actingAs($u)->get('/customer/pesanan/'.$o->id_pemesanan)->assertOk()->assertSee('Terkonfirmasi')->assertSee($o->kode_pemesanan);
        $this->actingAs(Umum::where('role', 'Staff')->first());
        $this->post('/staff/penugasan', ['id_pemesanan' => $o->id_pemesanan, 'id_driver' => 'DRV-002', 'id_kendaraan' => 'KND-003'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('JADWAL_PERJALANAN', ['id_pemesanan' => $o->id_pemesanan, 'id_driver' => 'DRV-002']);
    }

    public function test_admin_crud_and_driver_account_link()
    {
        $a = Umum::create(['id_pengguna' => 'ADMIN', 'nama_lengkap' => 'Admin', 'email' => 'admin@example.test', 'nomor_telepon' => '123', 'password' => bcrypt('secure123'), 'role' => 'Admin']);
        $this->actingAs($a);
        $this->post('/admin/kendaraan', ['plat_nomor' => 'B 999 XX', 'jenis_kendaraan' => 'Bus', 'merek' => 'Test', 'kapasitas' => 20, 'tahun_produksi' => 2024, 'status' => 'Tersedia'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('KENDARAAN', ['plat_nomor' => 'B 999 XX']);
        $this->post('/admin/driver', ['nama_lengkap' => 'New Driver', 'nomor_telepon' => '0812345678', 'nomor_sim' => 'SIMNEW', 'alamat' => 'Street', 'status' => 'Tersedia', 'email' => 'newdriver@example.test', 'password' => 'secure123', 'password_confirmation' => 'secure123'])->assertSessionHasNoErrors();
        $d = DB::table('DRIVER')->where('nomor_sim', 'SIMNEW')->first();
        $this->assertNotNull($d);
        $this->assertDatabaseHas('UMUM',['id_pengguna' => $d->FK_id_pengguna, 'role' => 'Driver']);
    }
}
