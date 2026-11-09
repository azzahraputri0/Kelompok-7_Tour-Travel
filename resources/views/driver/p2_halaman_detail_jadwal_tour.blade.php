@extends('layouts.app', ['title' => 'Detail Jadwal Tour', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/driver.css') }}">
@endpush

@php($d = $detailJadwalTour)

@section('content')
<div class="driver-detail">
    <a class="driver-back" href="{{ route('driver.jadwal') }}">&lt; Kembali</a>
    <h1 class="judul-hal">Detail Jadwal Tour</h1>
    <div class="driver-detail-grid">
        <section class="driver-blue driver-summary" aria-labelledby="driver-tour-name">
            <div class="driver-summary-content">
                <h2 id="driver-tour-name">{{ $d->nama_paket }}</h2>
                <p>{{ tanggal($d->tanggal_berangkat) }}</p>
                <p class="driver-location"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22S4 14 4 9a8 8 0 1 1 16 0c0 5-8 13-8 13Z" fill="currentColor"/><circle cx="12" cy="9" r="3" fill="var(--biru)"/></svg>{{ $d->tujuan }}</p>
                <span class="driver-summary-status">{{ $d->status }}</span>
            </div>
        </section>
        <section class="driver-blue driver-person" aria-labelledby="driver-data-title">
            <div class="driver-avatar" aria-label="Inisial driver">{{ mb_substr(auth()->user()->nama_lengkap, 0, 1) }}</div>
            <div class="driver-fields">
                <h2 id="driver-data-title">Data Driver</h2>
                <dl><dt>Nama Driver:</dt><dd>{{ auth()->user()->nama_lengkap }}</dd><dt>Nomor Telepon:</dt><dd>{{ auth()->user()->nomor_telepon ?: 'Tidak tersedia' }}</dd></dl>
            </div>
        </section>
        <section class="driver-blue driver-customer" aria-labelledby="driver-customer-title">
            <h2 id="driver-customer-title">Data Pemesan</h2>
            <dl><dt>Nama Pelanggan:</dt><dd>{{ $d->nama_pemesan }}</dd><dt>Nomor Telepon:</dt><dd>{{ $d->telepon_pemesan ?: 'Tidak tersedia' }}</dd><dt>Jumlah Peserta:</dt><dd>{{ $d->jumlah_peserta }} orang</dd></dl>
        </section>
        <section class="driver-blue driver-vehicle" aria-labelledby="driver-vehicle-title">
            <div class="driver-vehicle-placeholder" aria-label="Foto kendaraan tidak tersedia"><svg viewBox="0 0 160 100" aria-hidden="true"><path d="m30 48 12-25h66l17 25 17 7v25H18V57Zm15-17-8 18h78l-12-18ZM30 66h18m62 0h18" fill="none" stroke="currentColor" stroke-width="7" stroke-linejoin="round"/><circle cx="42" cy="81" r="10" fill="currentColor"/><circle cx="118" cy="81" r="10" fill="currentColor"/></svg><small>Foto tidak tersedia</small></div>
            <div class="driver-fields">
                <h2 id="driver-vehicle-title">Kendaraan</h2>
                <dl><dt>Plat Nomor:</dt><dd>{{ $d->plat_nomor }}</dd><dt>Merek:</dt><dd>{{ $d->merek }}</dd></dl>
            </div>
        </section>
        <section class="driver-blue driver-journey" aria-labelledby="driver-journey-title">
            <h2 id="driver-journey-title">Detail Perjalanan</h2>
            <div class="driver-journey-grid">
                <dl><dt>Keberangkatan:</dt><dd>{{ tanggal($d->tanggal_berangkat) }}</dd><dt>Durasi:</dt><dd>{{ $d->durasi }}</dd></dl>
                <dl><dt>Tujuan:</dt><dd>{{ $d->tujuan }}</dd></dl>
            </div>
        </section>
    </div>
</div>
@endsection
