<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('DRIVER', fn (Blueprint $t) => $t->string('foto_driver')->nullable());
        Schema::table('PEMBAYARAN', fn (Blueprint $t) => $t->string('metode_pembayaran', 30)->default('Transfer Bank'));
    }
    public function down(): void {
        Schema::table('DRIVER', fn (Blueprint $t) => $t->dropColumn('foto_driver'));
        Schema::table('PEMBAYARAN', fn (Blueprint $t) => $t->dropColumn('metode_pembayaran'));
    }
};
