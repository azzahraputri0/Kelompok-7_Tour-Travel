@extends('layouts.app', ['title' => 'Jadwal Tour'])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/driver.css') }}">
@endpush

@section('content')
    <div class="driver-schedule">
    @if ($pesan)
        <div class="kosong">{{ $pesan }}</div>
    @else
        <div class="driver-table" role="table" aria-label="Jadwal Tour">
            <div class="driver-table-head" role="row"><span role="columnheader">Kode</span><span role="columnheader">Paket Travel</span><span role="columnheader">Tujuan</span><span role="columnheader">Berangkat</span><span role="columnheader">Detail</span></div>
            @foreach ($daftarJadwalTour as $j)
                <div class="driver-table-row" role="row">
                    <span role="cell" data-label="Kode">{{ $j->kode_pemesanan }}</span>
                    <span role="cell" data-label="Paket Travel">{{ $j->nama_paket }}</span>
                    <span role="cell" data-label="Tujuan">{{ $j->tujuan }}</span>
                    <span role="cell" data-label="Berangkat">{{ \Illuminate\Support\Carbon::parse($j->tanggal_berangkat)->translatedFormat('j M Y') }}</span>
                    <span role="cell"><a class="driver-detail-link" href="{{ route('driver.jadwal.detail', $j->id_jadwal) }}" aria-label="Detail {{ $j->nama_paket }}">Detail</a></span>
                </div>
            @endforeach
        </div>
    @endif
    </div>
@endsection
