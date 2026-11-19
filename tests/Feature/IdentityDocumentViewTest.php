<?php

namespace Tests\Feature;

use App\Models\Umum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdentityDocumentViewTest extends TestCase
{
    use RefreshDatabase;
    protected bool $seed = true;

    public function test_uploaded_identity_uses_authorized_file_route_in_customer_detail(): void
    {
        Storage::fake('local');
        $customer = Umum::where('role', 'Customer')->first();
        Storage::disk('local')->put('identitas/qa.pdf', '%PDF-1.4 demo');
        DB::table('UMUM')->where('id_pengguna', $customer->id_pengguna)->update(['dokumen_identitas' => 'identitas/qa.pdf']);
        $this->actingAs(Umum::where('role', 'Staff')->first());
        $this->get('/pelanggan/'.$customer->id_pengguna)->assertOk()
            ->assertSee(route('berkas.identitas', $customer->id_pengguna), false)
            ->assertDontSee(url('identitas/qa.pdf'), false);
        $this->get('/berkas/identitas/'.$customer->id_pengguna)->assertOk();
        $other = Umum::where('role', 'Customer')->where('id_pengguna', '!=', $customer->id_pengguna)->first();
        $this->actingAs($other)->get('/berkas/identitas/'.$customer->id_pengguna)->assertNotFound();
    }
}
