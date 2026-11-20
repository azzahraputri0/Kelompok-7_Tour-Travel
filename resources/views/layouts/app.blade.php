@php
    $user = auth()->user() ?? (object) ['role' => 'Guest'];
    $ikon = [
        'dashboard' => '<path d="M4 4h7v7H4zm9 0h7v7h-7zM4 13h7v7H4zm9 0h7v7h-7z" fill="currentColor" rx="2"/>',
        'jadwal' => '<path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2zm-2 8h14v10H5zm2 2v2h4v-2zm6 0v2h4v-2zm-6 4v2h4v-2z" fill="currentColor"/>',
        'laporan' => '<path d="M6 2h9l5 5v15H6zm8 1.5V8h4.5zM8 12v2h8v-2zm0 4v2h8v-2z" fill="currentColor"/>',
        'pelanggan' => '<path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8m7 0a3 3 0 1 0 0-6v6zM1 20c0-3.3 3.6-6 8-6s8 2.7 8 6v1H1zm17 1v-1c0-1.7-.7-3.2-1.9-4.4 3.3.4 5.9 2.3 5.9 4.4v1z" fill="currentColor"/>',
        'pemesanan' => '<path d="M12 2a6 6 0 0 0-6 6v4l-2 4h16l-2-4V8a6 6 0 0 0-6-6m-2 16a2 2 0 0 0 4 0z" fill="currentColor"/>',
        'pembayaran' => '<path d="M4 3h16a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 1-2m3 5v2h10V8zm0 4v2h7v-2z" fill="currentColor"/>',
    ];
    $menu = match ($user->role) {
        'Admin' => [
            ['admin.dashboard', 'Dashboard', 'dashboard', 'admin.dashboard'],
            ['admin.paket.index', 'Data Master', 'pelanggan', 'admin.paket.*,admin.driver.*,admin.kendaraan.*'],
            ['admin.penugasan.index', 'Penugasan', 'jadwal', 'admin.penugasan.*'],
        ],
        'Customer' => [
            ['katalog.index', 'Paket Travel', 'jadwal', 'katalog.*,customer.pesanan.create,customer.pesanan.penumpang,customer.pesanan.store'],
            ['customer.pesanan.index', 'Pesanan Saya', 'pemesanan', 'customer.pesanan.index,customer.pesanan.detail,customer.pembayaran.*'],
        ],
        'Guest' => [
            ['katalog.index', 'Paket Travel', 'jadwal', 'katalog.*'],
            ['login', 'Masuk', 'pelanggan', 'login'],
            ['register', 'Daftar', 'pelanggan', 'register'],
        ],
        'Driver' => [
            ['driver.dashboard', 'Dashboard', 'dashboard', 'driver.dashboard'],
            ['driver.jadwal', 'Jadwal Tour', 'jadwal', 'driver.jadwal*'],
        ],
        'Manager' => [
            ['manager.dashboard', 'Dashboard', 'dashboard', 'manager.dashboard'],
            ['manager.laporan', 'Laporan', 'laporan', 'manager.laporan*'],
        ],
        default => [
            ['staff.dashboard', 'Dashboard', 'dashboard', 'staff.dashboard'],
            ['pelanggan.index', 'Pelanggan', 'pelanggan', 'pelanggan.*'],
            ['staff.pemesanan.index', 'Pemesanan', 'pemesanan', 'staff.pemesanan.*'],
            ['staff.pembayaran.index', 'Pembayaran', 'pembayaran', 'staff.pembayaran.*'],
            ['staff.laporan', 'Laporan', 'laporan', 'staff.laporan*'],
        ],
    };
    $popup = session('success') ? ['sukses', session('popup_judul', 'Pesan Berhasil'), session('success')]
        : (session('error') ? ['gagal', session('popup_judul', 'Pesan Gagal'), session('error')]
        : ($errors->any() ? ['gagal', 'Pesan Gagal', $errors->first()] : null));
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Travel Tour' }} · Travel Tour</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/travel.css') }}">
    @stack('head')
</head>
<body>
<div class="app" id="app">
    <aside class="sidebar" id="sidebar" aria-label="Menu utama">
        <a class="brand" href="{{ ($user->role === 'Guest' ? route('katalog.index') : route(strtolower($user->role).'.dashboard')) }}">Travel<span>Tour</span></a>
        <nav class="menu">
            @foreach ($menu as [$route, $label, $icon, $pola])
                @if ($route)
                    <a href="{{ route($route) }}" class="{{ request()->routeIs(...explode(',', $pola)) ? 'aktif' : '' }}" @if (request()->routeIs(...explode(',', $pola))) aria-current="page" @endif>
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $ikon[$icon] !!}</svg>{{ $label }}
                    </a>
                @else
                    <a role="link" aria-disabled="true" title="Modul {{ $label }} dikerjakan anggota tim lain">
                        <svg viewBox="0 0 24 24" aria-hidden="true">{!! $ikon[$icon] !!}</svg>{{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>
        <hr class="garis-menu">
        <div class="peran">{{ $user->role }}</div>
        @auth
        <form class="keluar-form" method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="keluar">Keluar</button>
        </form>
        @endauth
    </aside>

    <main class="konten">
        <button type="button" class="buka-menu" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false" id="tombol-menu">
            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z" fill="currentColor"/></svg>
        </button>
        @if (! ($sembunyikanJudul ?? false))
            <h1 class="judul-hal">{{ $title ?? 'Dashboard' }}</h1>
        @endif

        @yield('content')
    </main>
</div>

@if ($popup)
    <div class="popup-latar" id="popup" role="dialog" aria-modal="true" aria-labelledby="popup-judul">
        <div class="popup {{ $popup[0] }}">
            <button type="button" class="popup-tutup" id="popup-tutup" aria-label="Tutup pesan">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19" stroke="#000" stroke-width="3.5" stroke-linecap="round"/></svg>
            </button>
            <h2 id="popup-judul">{{ $popup[1] }}</h2>
            <p>{{ $popup[2] }}</p>
        </div>
    </div>
@endif
<script>
    (function () {
        var app = document.getElementById('app'), tombol = document.getElementById('tombol-menu');
        tombol.addEventListener('click', function (e) {
            e.stopPropagation();
            tombol.setAttribute('aria-expanded', app.classList.toggle('menu-buka'));
        });
        document.addEventListener('click', function (e) {
            if (app.classList.contains('menu-buka') && !document.getElementById('sidebar').contains(e.target)) {
                app.classList.remove('menu-buka');
                tombol.setAttribute('aria-expanded', 'false');
            }
        });
        var popup = document.getElementById('popup');
        if (popup) {
            var tutup = function () { popup.hidden = true; };
            document.getElementById('popup-tutup').addEventListener('click', tutup);
            document.getElementById('popup-tutup').focus();
            popup.addEventListener('click', function (e) { if (e.target === popup) tutup(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutup(); });
        }
    })();
</script>
<script src="{{ asset('assets/travel.js') }}"></script>
@stack('scripts')
</body>
</html>
