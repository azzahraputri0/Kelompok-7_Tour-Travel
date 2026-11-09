<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Struktur tabel mengikuti DDL tim (Modul Evaluasi 3.1) dan PDM untuk JADWAL_PERJALANAN.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('UMUM', function (Blueprint $t) {
            $t->string('id_pengguna', 50)->primary();
            $t->string('nama_lengkap', 50);
            $t->string('email', 50)->unique();
            $t->string('nomor_telepon', 50);
            $t->string('password', 50);
            $t->string('role', 50);
        });

        Schema::create('PAKET_TRAVEL', function (Blueprint $t) {
            $t->string('id_paket', 50)->primary();
            $t->string('nama_paket', 50);
            $t->string('tujuan', 50);
            $t->string('durasi', 50);
            $t->decimal('harga', 12, 2);
            $t->integer('kuota');
            $t->date('jadwal_keberangkatan');
            $t->string('deskripsi', 50)->nullable();
            $t->string('foto_paket', 300)->nullable();
        });

        Schema::create('KENDARAAN', function (Blueprint $t) {
            $t->string('id_kendaraan', 50)->primary();
            $t->string('plat_nomor', 20)->unique();
            $t->string('jenis_kendaraan', 50);
            $t->string('merek', 50);
            $t->integer('kapasitas');
            $t->integer('tahun_produksi');
            $t->string('status', 50);
        });

        Schema::create('DRIVER', function (Blueprint $t) {
            $t->string('id_driver', 50)->primary();
            $t->string('nama_lengkap', 100);
            $t->string('nomor_telepon', 20);
            $t->string('nomor_sim', 50)->unique();
            $t->string('alamat', 255);
            $t->string('status', 50);
        });

        // DDL tim menulis FK PEMESANAN ke dirinya sendiri; relasi di bawah mengikuti PDM.
        Schema::create('PEMESANAN', function (Blueprint $t) {
            $t->string('id_pemesanan', 50)->primary();
            $t->string('kode_pemesanan', 50)->nullable();
            $t->string('FK_id_pengguna', 50)->nullable();
            $t->string('FK_id_paket', 50)->nullable();
            $t->date('jadwal_keberangkatan')->nullable();
            $t->integer('jumlah_peserta')->nullable();
            $t->decimal('total_pembayaran', 10, 2)->nullable();
            $t->string('status', 50)->nullable();
            $t->string('status_kuota', 50)->nullable();
            $t->date('batas_waktu_pembayaran')->nullable();
            $t->foreign('FK_id_pengguna')->references('id_pengguna')->on('UMUM');
            $t->foreign('FK_id_paket')->references('id_paket')->on('PAKET_TRAVEL');
        });

        Schema::create('PEMBAYARAN', function (Blueprint $t) {
            $t->string('id_pembayaran', 50)->primary();
            $t->string('nomor_invoice', 50)->nullable();
            $t->string('id_pemesanan', 50)->nullable();
            $t->decimal('nominal', 10, 2)->nullable();
            $t->string('bukti_pembayaran', 50)->nullable();
            $t->date('tanggal_bayar')->nullable();
            $t->string('status', 50)->nullable();
            $t->foreign('id_pemesanan')->references('id_pemesanan')->on('PEMESANAN');
        });

        Schema::create('JADWAL_PERJALANAN', function (Blueprint $t) {
            $t->string('id_jadwal', 50)->primary();
            $t->string('id_pemesanan', 50);
            $t->string('id_kendaraan', 50);
            $t->string('id_driver', 50);
            $t->date('tanggal_berangkat');
            $t->string('status', 50);
            $t->foreign('id_pemesanan')->references('id_pemesanan')->on('PEMESANAN');
            $t->foreign('id_kendaraan')->references('id_kendaraan')->on('KENDARAAN');
            $t->foreign('id_driver')->references('id_driver')->on('DRIVER');
        });
    }

    public function down(): void
    {
        foreach (['JADWAL_PERJALANAN', 'PEMBAYARAN', 'PEMESANAN', 'DRIVER', 'KENDARAAN', 'PAKET_TRAVEL', 'UMUM'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
