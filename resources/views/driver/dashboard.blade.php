@extends('layouts.app', ['title' => 'Dashboard', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/driver.css') }}">
@endpush

@section('content')
<div class="driver-dashboard">
    <section class="driver-hero">
        <h1>Halo, {{ auth()->user()->nama_lengkap }}!</h1>
        <p>Selamat menjalankan tugas hari ini.</p>
    </section>
    <div class="driver-kpis">
        <section><strong>{{ $bulanIni }}</strong><h2>Tugas<br>Bulan Ini</h2></section>
        <section><strong>{{ $selesai }}</strong><h2>Tugas<br>Selesai</h2></section>
        <section><strong>{{ $mendatang }}</strong><h2>Tugas<br>Mendatang</h2></section>
    </div>
    <div class="driver-dashboard-grid">
        <section class="driver-rekap driver-blue">
            <header><h2>Rekap Perjalanan</h2><span class="driver-month">{{ now()->translatedFormat('F Y') }}</span></header>
            <div class="driver-chart-layout">
                <div class="driver-chart-tile"><div class="driver-donut" style="--progress:{{ $persen }}%" role="img" aria-label="{{ $persen }} persen tugas selesai"><div><strong>{{ $persen }}%</strong><span>Selesai</span></div></div></div>
                <dl class="driver-legend"><div><dt>Selesai</dt><dd>{{ $selesai }} Tugas</dd></div><div><dt>Mendatang</dt><dd>{{ $mendatang }} Tugas</dd></div></dl>
            </div>
        </section>
        <section class="driver-upcoming driver-blue">
            <h2>Jadwal Terdekat</h2>
            <div class="driver-upcoming-list">
            @forelse ($terdekat as $j)
                <a class="driver-trip" href="{{ route('driver.jadwal.detail', $j->id_jadwal) }}">
                    <time class="driver-date" datetime="{{ $j->tanggal_berangkat }}"><b>{{ \Illuminate\Support\Carbon::parse($j->tanggal_berangkat)->format('d') }}</b><span>{{ \Illuminate\Support\Carbon::parse($j->tanggal_berangkat)->translatedFormat('F') }}</span></time>
                    <span class="driver-trip-name"><b>{{ $j->nama_paket }}</b><small>{{ $j->tujuan }}</small></span>
                    <span class="driver-status">{{ $j->status }}</span>
                </a>
            @empty
                <div class="kosong">Jadwal Tour tidak tersedia</div>
            @endforelse
            </div>
        </section>
    </div>
    <section class="driver-notice"><h2>Selamat Bertugas!</h2><p>Pastikan selalu mengecek detail jadwal dan berkomunikasi dengan tim jika ada kendala.</p></section>
</div>
@endsection
