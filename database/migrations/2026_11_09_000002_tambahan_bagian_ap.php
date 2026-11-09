<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tambahan di luar DDL tim yang dibutuhkan bagian AP. Dipisah supaya mudah disesuaikan saat digabung.
// 1. UMUM.password 255 karakter: hash bcrypt (60 karakter) tidak muat di VARCHAR(50).
// 2. UMUM.nomor_identitas & dokumen_identitas: data detail pelanggan untuk UC-12.
// 3. DRIVER.FK_id_pengguna: menghubungkan akun login driver dengan data driver (sesuai PDM), untuk UC-16.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('UMUM', function (Blueprint $t) {
            $t->string('password', 255)->change();
            $t->string('nomor_identitas', 50)->nullable();
            $t->string('dokumen_identitas', 300)->nullable();
        });

        Schema::table('DRIVER', function (Blueprint $t) {
            $t->string('FK_id_pengguna', 50)->nullable();
            $t->foreign('FK_id_pengguna')->references('id_pengguna')->on('UMUM');
        });
    }

    public function down(): void
    {
        Schema::table('DRIVER', function (Blueprint $t) {
            $t->dropForeign(['FK_id_pengguna']);
            $t->dropColumn('FK_id_pengguna');
        });

        Schema::table('UMUM', function (Blueprint $t) {
            $t->dropColumn(['nomor_identitas', 'dokumen_identitas']);
            $t->string('password', 50)->change();
        });
    }
};
