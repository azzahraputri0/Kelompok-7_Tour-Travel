<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalTour_Kontrol;
use App\Http\Controllers\Laporan_Kontrol;
use App\Http\Controllers\MasterData_Kontrol;
use App\Http\Controllers\PaketTravel_Kontrol;
use App\Http\Controllers\Pelanggan_Kontrol;
use App\Http\Controllers\Pembayaran_Kontrol;
use App\Http\Controllers\Pemesanan_Kontrol;
use App\Http\Controllers\Penugasan_Kontrol;
use App\Http\Controllers\ValidasiData_Kontrol;
use Illuminate\Support\Facades\Route;

Route::get('/', [PaketTravel_Kontrol::class, 'index'])->name('home');

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

Route::get('/paket', [PaketTravel_Kontrol::class, 'index'])->name('katalog.index');
Route::get('/paket/{id}', [PaketTravel_Kontrol::class, 'detail'])->name('katalog.detail');
Route::get('/foto-paket/{id}', [PaketTravel_Kontrol::class, 'foto'])->name('katalog.foto');
Route::middleware('guest')->group(function () {
    Route::get('/register', [ValidasiData_Kontrol::class, 'registerForm'])->name('register');
    Route::post('/register', [ValidasiData_Kontrol::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});
Route::middleware('auth')->group(function () {
    Route::get('/berkas/pembayaran/{id}', [Pembayaran_Kontrol::class, 'bukti'])->name('berkas.pembayaran');
    Route::get('/berkas/identitas/{id}', [Pembayaran_Kontrol::class, 'identitas'])->name('berkas.identitas');
});
Route::middleware(['auth', 'role:Customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('customer.pesanan.index'))->name('dashboard');
    Route::get('/pemesanan/{idPaket}/buat', [Pemesanan_Kontrol::class, 'create'])->name('pesanan.create');
    Route::post('/pemesanan/{idPaket}/penumpang', [Pemesanan_Kontrol::class, 'penumpang'])->name('pesanan.penumpang');
    Route::post('/pemesanan/{idPaket}', [Pemesanan_Kontrol::class, 'store'])->name('pesanan.store');
    Route::get('/pesanan', [Pemesanan_Kontrol::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/{id}', [Pemesanan_Kontrol::class, 'detail'])->name('pesanan.detail');
    Route::get('/pesanan/{id}/bayar', [Pembayaran_Kontrol::class, 'create'])->name('pembayaran.create');
    Route::post('/pesanan/{id}/bayar', [Pembayaran_Kontrol::class, 'store'])->name('pembayaran.store');
});
Route::middleware(['auth', 'role:Staff'])->prefix('staff')->name('staff.')->group(function () {
    foreach (['pemesanan' => Pemesanan_Kontrol::class, 'pembayaran' => Pembayaran_Kontrol::class] as $resource => $controller) {
        Route::get('/'.$resource, [$controller, $resource === 'pemesanan' ? 'staffIndex' : 'index'])->name($resource.'.index');
        Route::get('/'.$resource.'/{id}', [$controller, $resource === 'pemesanan' ? 'staffDetail' : 'detail'])->name($resource.'.detail');
        foreach (['verifikasi', 'tolak'] as $action) {
            Route::post('/'.$resource.'/{id}/'.$action, [$controller, $action])->name($resource.'.'.$action);
        }
    }
    Route::get('/penugasan', [Penugasan_Kontrol::class, 'index'])->name('penugasan.index');
    Route::post('/penugasan', [Penugasan_Kontrol::class, 'store'])->name('penugasan.store');
    Route::delete('/penugasan/{id}', [Penugasan_Kontrol::class, 'destroy'])->name('penugasan.destroy');
});
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [MasterData_Kontrol::class, 'dashboard'])->name('dashboard');
    foreach (['paket' => 'PaketTravel', 'kendaraan' => 'Kendaraan', 'driver' => 'Driver'] as $resource => $class) {
        $controller = 'App\\Http\\Controllers\\'.$class.'_Kontrol';
        $save = $resource === 'paket' ? 'validasiPaket' : 'validasiTambah'.$class;
        $update = $resource === 'paket' ? 'validasiPaket' : 'validasiEdit'.$class;
        $delete = $resource === 'paket' ? 'hapusPaket' : 'hapus'.$class;
        foreach ([['get', '', 'getDaftar'.$class, 'index'], ['get', '/create', 'get'.$class, 'create'], ['post', '', $save, 'store'], ['get', '/{id}/edit', 'get'.$class, 'edit'], ['put', '/{id}', $update, 'update'], ['delete', '/{id}', $delete, 'destroy']] as [$verb,$suffix,$action,$name]) {
            Route::$verb('/'.$resource.$suffix, [$controller, $action])->name($resource.'.'.$name);
        }
    }
});
