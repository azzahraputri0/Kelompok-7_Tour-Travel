<?php

namespace Tests\Feature;

use App\Entities\Pemesanan_Entity;
use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TravelInvariantsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function user($role = 'Customer')
    {
        return Umum::where('role', $role)->first();
    }

    private function book()
    {
        $p = DB::table('PAKET_TRAVEL')->first();
        $u = $this->user();
        $this->actingAs($u)->post('/customer/pemesanan/'.$p->id_paket, ['jadwal_keberangkatan' => $p->jadwal_keberangkatan, 'jumlah_peserta' => 1, 'email' => $u->email, 'no_hp' => '0812345678', 'total_pembayaran' => 1, 'penumpang' => [['nama_lengkap' => 'Passenger', 'nik' => '1234567890123456', 'tanggal_lahir' => '2000-01-01']]])->assertSessionHasNoErrors();

        return DB::table('PEMESANAN')->where('email', $u->email)->first();
    }

    private function upload($id)
    {
        return $this->actingAs($this->user())->post('/customer/pesanan/'.$id.'/bayar', ['bukti_pembayaran' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf')]);
    }

    public function test_unpaid_cannot_confirm_and_price_cannot_be_tampered()
    {
        $o = $this->book();
        $this->assertGreaterThan(1, $o->total_pembayaran);
        $this->actingAs($this->user('Staff'))->post('/staff/pemesanan/'.$o->id_pemesanan.'/verifikasi', ['link_grup_wa' => 'https://chat.whatsapp.com/Group'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('PEMESANAN', ['id_pemesanan' => $o->id_pemesanan, 'status' => 'Belum Terbayar']);
    }

    public function test_rejection_requires_reason_and_allows_proof_retry()
    {
        Storage::fake('local');
        $o = $this->book();
        $this->upload($o->id_pemesanan)->assertSessionHasNoErrors();
        $p = DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->first();
        $this->upload($o->id_pemesanan)->assertSessionHasErrors('status');
        $this->actingAs($this->user('Staff'))->post('/staff/pembayaran/'.$p->id_pembayaran.'/tolak')->assertSessionHasErrors('alasan');
        $this->post('/staff/pembayaran/'.$p->id_pembayaran.'/tolak', ['alasan' => 'Nominal tidak sesuai'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('PEMBAYARAN', ['id_pembayaran' => $p->id_pembayaran, 'status' => 'Ditolak', 'alasan' => 'Nominal tidak sesuai']);
        $this->upload($o->id_pemesanan)->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->count());
        $this->assertDatabaseHas('PEMBAYARAN', ['id_pembayaran' => $p->id_pembayaran, 'status' => 'Proses Verifikasi', 'alasan' => null]);
    }

    public function test_expiry_is_idempotent_and_releases_only_unpaid_quota()
    {
        $o = $this->book();
        $e = new Pemesanan_Entity;
        $before = $e->reserved($o->FK_id_paket);
        DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['kedaluwarsa_pada' => now()->subMinute()]);
        $this->artisan('travel:expire-bookings')->expectsOutput('Expired: 1')->assertSuccessful();
        $this->artisan('travel:expire-bookings')->expectsOutput('Expired: 0')->assertSuccessful();
        $this->assertSame($before - 1, $e->reserved($o->FK_id_paket));
        $this->upload($o->id_pemesanan)->assertSessionHasErrors('status');
    }

    public function test_private_files_and_orders_enforce_owner()
    {
        Storage::fake('local');
        $o = $this->book();
        $this->upload($o->id_pemesanan);
        $p = DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->first();
        $this->get('/berkas/pembayaran/'.$p->id_pembayaran)->assertOk();
        $other = Umum::where('role', 'Customer')->where('id_pengguna', '!=', $o->FK_id_pengguna)->first();
        $this->actingAs($other)->get('/customer/pesanan/'.$o->id_pemesanan)->assertNotFound();
        $this->get('/berkas/pembayaran/'.$p->id_pembayaran)->assertNotFound();
        $this->post('/customer/pesanan/'.$o->id_pemesanan.'/bayar', ['bukti_pembayaran' => UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf')])->assertNotFound();
        $this->actingAs($this->user('Manager'))->get('/berkas/pembayaran/'.$p->id_pembayaran)->assertNotFound();
        $this->actingAs($this->user('Staff'))->get('/berkas/pembayaran/'.$p->id_pembayaran)->assertOk();
    }

    public function test_quota_passengers_and_role_boundaries()
    {
        $p = DB::table('PAKET_TRAVEL')->first();
        $this->actingAs($this->user())->post('/customer/pemesanan/'.$p->id_paket, ['jadwal_keberangkatan' => $p->jadwal_keberangkatan, 'jumlah_peserta' => 100])->assertSessionHasErrors('jumlah_peserta');
        $this->post('/customer/pemesanan/'.$p->id_paket, ['jadwal_keberangkatan' => $p->jadwal_keberangkatan, 'jumlah_peserta' => 1, 'email' => 'x@example.test', 'no_hp' => '1', 'penumpang' => []])->assertSessionHasErrors('penumpang');
        $this->post('/admin/kendaraan', [])->assertForbidden();
        $this->post('/staff/penugasan', [])->assertForbidden();
    }

    public function test_registration_duplicate_and_invalid_upload_rejected()
    {
        $d = ['nama_lengkap' => 'User', 'email' => $this->user()->email, 'nomor_telepon' => '1', 'password' => 'secure123', 'password_confirmation' => 'secure123'];
        $this->post('/register', $d)->assertSessionHasErrors('email');
        $this->book();
        $o = DB::table('PEMESANAN')->whereNotNull('email')->first();
        $this->post('/customer/pesanan/'.$o->id_pemesanan.'/bayar', ['bukti_pembayaran' => UploadedFile::fake()->create('shell.php', 10, 'text/plain')])->assertSessionHasErrors('bukti_pembayaran');
    }

    public function test_assignment_rejects_capacity_duplicate_and_overlap()
    {
        $o = $this->book();
        DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['status' => 'Terkonfirmasi', 'jumlah_peserta' => 99]);
        $this->actingAs($this->user('Staff'));
        $d = ['id_pemesanan' => $o->id_pemesanan, 'id_driver' => 'DRV-002', 'id_kendaraan' => 'KND-003'];
        $this->post('/staff/penugasan', $d)->assertSessionHasErrors('id_kendaraan');
        DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['jumlah_peserta' => 1]);
        $this->post('/staff/penugasan', $d)->assertSessionHasNoErrors();
        $this->post('/staff/penugasan', $d)->assertSessionHasErrors('id_pemesanan');
        $copy = (array) $o;
        $copy['id_pemesanan'] = 'OTHER';
        $copy['kode_pemesanan'] = 'OTHER';
        $copy['status'] = 'Terkonfirmasi';
        DB::table('PEMESANAN')->insert($copy);
        $d['id_pemesanan'] = 'OTHER';
        $this->post('/staff/penugasan', $d)->assertSessionHasErrors('id_driver');
    }

    public function test_admin_update_delete_and_referenced_delete_guard()
    {
        $u = $this->user();
        $u->role = 'Admin';
        $u->save();
        $this->actingAs($u);
        $d = ['nama_paket' => 'New', 'tujuan' => 'Bali', 'durasi' => '2 hari', 'harga' => 100000, 'kuota' => 10, 'jadwal_keberangkatan' => today()->addMonths(2)->toDateString()];
        $this->post('/admin/paket', $d)->assertSessionHasNoErrors();
        $p = DB::table('PAKET_TRAVEL')->where('nama_paket', 'New')->first();
        $this->assertNotNull($p);
        $this->put('/admin/paket/'.$p->id_paket, array_merge($d, ['nama_paket' => 'Updated']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('PAKET_TRAVEL', ['id_paket' => $p->id_paket, 'nama_paket' => 'Updated']);
        $this->delete('/admin/paket/'.$p->id_paket)->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('PAKET_TRAVEL', ['id_paket' => $p->id_paket]);
        $used = DB::table('PEMESANAN')->first()->FK_id_paket;
        $this->delete('/admin/paket/'.$used)->assertSessionHasErrors('hapus');
    }

    public function test_paid_orders_do_not_expire_while_waiting_for_confirmation()
    {
        Storage::fake('local');
        $o = $this->book();
        $this->upload($o->id_pemesanan);
        $p = DB::table('PEMBAYARAN')->where('id_pemesanan', $o->id_pemesanan)->first();
        $this->actingAs($this->user('Staff'))->post('/staff/pembayaran/'.$p->id_pembayaran.'/verifikasi')->assertSessionHasNoErrors();
        DB::table('PEMESANAN')->where('id_pemesanan', $o->id_pemesanan)->update(['kedaluwarsa_pada' => now()->subDay()]);
        $this->assertSame(0,(new Pemesanan_Entity)->expire());
        $this->assertDatabaseHas('PEMESANAN',['id_pemesanan' => $o->id_pemesanan, 'status_kuota' => 'Kuota Terkunci']);
    }
}
