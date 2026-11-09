@extends('layouts.app', ['title' => 'Dashboard'])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
@endpush

@section('content')
    <div class="staff-dashboard">
        <section class="staff-welcome">
            <div>
                <h2>Halo, {{ auth()->user()->nama_lengkap }}</h2>
                <p>Pantau data pelanggan dan kirim laporan transaksi ke Manager.</p>
                <div class="staff-actions">
                    <a class="btn lime" href="{{ route('staff.laporan') }}">Buka Laporan</a>
                    <a class="btn staff-secondary" href="{{ route('pelanggan.index') }}">Data Pelanggan</a>
                </div>
            </div>
            <img src="{{ asset('assets/img/paket-bali.jpg') }}" alt="">
        </section>

        <div class="dashboard-metrics">
            <div class="dashboard-metric"><h2>Total Pelanggan</h2><strong>{{ $totalPelanggan }}</strong></div>
            <div class="dashboard-metric"><h2>Transaksi Bulan Ini</h2><strong>{{ $transaksiBulanIni }}</strong></div>
            <div class="dashboard-metric"><h2>Menunggu Verifikasi</h2><strong>{{ $menungguVerifikasi }}</strong></div>
        </div>
    </div>
@endsection
