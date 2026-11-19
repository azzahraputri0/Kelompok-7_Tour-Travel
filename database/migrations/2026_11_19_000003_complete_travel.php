<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PAKET_TRAVEL', function (Blueprint $t) {
            $t->text('deskripsi')->nullable()->change();
            $t->text('fasilitas')->nullable();
            $t->decimal('biaya_admin', 12, 2)->default(0);
        });
        Schema::table('PEMESANAN', function (Blueprint $t) {
            $t->string('email')->nullable();
            $t->string('no_hp', 50)->nullable();
            $t->json('penumpang')->nullable();
            $t->string('link_grup_wa')->nullable();
            $t->text('alasan')->nullable();
            $t->timestamp('kedaluwarsa_pada')->nullable();
        });
        Schema::table('PEMBAYARAN', function (Blueprint $t) {
            $t->string('bukti_pembayaran', 300)->nullable()->change();
            $t->text('alasan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('PEMBAYARAN', fn (Blueprint $t) => $t->dropColumn('alasan'));
        Schema::table('PEMESANAN', fn (Blueprint $t) => $t->dropColumn(['email', 'no_hp', 'penumpang', 'link_grup_wa', 'alasan', 'kedaluwarsa_pada']));
        Schema::table('PAKET_TRAVEL', fn (Blueprint $t) => $t->dropColumn(['fasilitas', 'biaya_admin']));
    }
};
