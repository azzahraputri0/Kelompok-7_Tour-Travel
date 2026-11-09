<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalTour_Kontrol;
use App\Http\Controllers\Laporan_Kontrol;
use App\Http\Controllers\Pelanggan_Kontrol;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect(AuthController::beranda(auth()->user()->role))
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Staff: UC-10 Melihat Laporan, UC-11 Mengirim Laporan
Route::middleware(['auth', 'role:Staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'staff'])->name('dashboard');
    Route::get('/laporan', [Laporan_Kontrol::class, 'getAll_transaksi'])->name('laporan');
    Route::post('/laporan/kirim', [Laporan_Kontrol::class, 'kirim_laporan_transaksi'])->name('laporan.kirim');
});

// UC-12 Memeriksa Data Pelanggan: Staff, dan Manager sebagai generalisasi Staff
Route::middleware(['auth', 'role:Staff,Manager'])->prefix('pelanggan')->name('pelanggan.')->group(function () {
    Route::get('/', [Pelanggan_Kontrol::class, 'getAll_pelanggan'])->name('index');
    Route::get('/cari', [Pelanggan_Kontrol::class, 'cari_pelanggan'])->name('cari');
    Route::get('/{idPelanggan}', [Pelanggan_Kontrol::class, 'get_detail_pelanggan'])->name('detail');
});

// Driver: UC-16 Melihat Jadwal Tour
Route::middleware(['auth', 'role:Driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'driver'])->name('dashboard');
    Route::get('/jadwal-tour', [JadwalTour_Kontrol::class, 'getAll_jadwalTour'])->name('jadwal');
    Route::get('/jadwal-tour/{idJadwal}', [JadwalTour_Kontrol::class, 'get_detail_jadwalTour'])->name('jadwal.detail');
});

// Manager: UC-17 Meninjau Laporan, UC-18 Mengekspor Laporan
Route::middleware(['auth', 'role:Manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');
    Route::get('/laporan', [Laporan_Kontrol::class, 'getAll_transaksi'])->name('laporan');
    Route::get('/laporan/ekspor', [Laporan_Kontrol::class, 'ekspor_laporan'])->name('laporan.ekspor');
});
