@extends('layouts.app', ['title' => 'Detail Data Pelanggan', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/pelanggan.css') }}">
@endpush

@php
    $p = $detailPelanggan;
    $dokumen = $p->dokumen_identitas;
    $gambar = $dokumen && preg_match('/\.(jpe?g|png|webp|svg)$/i', $dokumen);
    $urlDokumen = $dokumen && str_starts_with($dokumen, 'identitas/')
        ? route('berkas.identitas', $p->id_pengguna)
        : ($dokumen === 'uploads/identitas/ktp-contoh.svg' ? asset($dokumen) : null);
@endphp

@section('content')
    <div class="pelanggan-detail">
        <a class="pelanggan-back" href="{{ route('pelanggan.index') }}">&lt; Kembali</a>
        <h1 class="pelanggan-title">Detail Data Pelanggan</h1>
        <section class="pelanggan-profile" aria-labelledby="judul-profil">
            <h2 id="judul-profil">Data Pelanggan</h2>
            <dl>
                <div><dt>Nama Pelanggan:</dt><dd>{{ $p->nama_lengkap }}</dd></div>
                <div><dt>Nomor Telepon:</dt><dd>{{ $p->nomor_telepon }}</dd></div>
                <div><dt>Alamat Email:</dt><dd>{{ $p->email }}</dd></div>
            </dl>
        </section>

        <section class="pelanggan-additional" aria-labelledby="judul-identitas">
            <h2 id="judul-identitas">Identitas Pelanggan</h2>
            <dl class="pelanggan-identity">
                <div><dt>Nomor Identitas</dt><dd>{{ $p->nomor_identitas ?: '-' }}</dd></div>
                <div><dt>Status Data</dt><dd><span class="pelanggan-status {{ $p->status_data === 'Terverifikasi' ? 'verified' : 'incomplete' }}">{{ $p->status_data }}</span></dd></div>
            </dl>
            <h3>Dokumen Identitas</h3>
            @if ($gambar && $urlDokumen)
                <a href="{{ $urlDokumen }}" target="_blank" rel="noopener"><img class="pelanggan-document" src="{{ $urlDokumen }}" alt="Dokumen identitas {{ $p->nama_lengkap }}"></a>
            @elseif ($urlDokumen)
                <a class="btn" href="{{ $urlDokumen }}" target="_blank" rel="noopener">Lihat dokumen</a>
            @else
                <div class="kosong">Dokumen identitas belum diunggah.</div>
            @endif
        </section>

        <section class="pelanggan-history" aria-labelledby="judul-riwayat">
            <h2 id="judul-riwayat">Riwayat Pemesanan</h2>
            @if ($p->riwayat->isEmpty())
                <div class="kosong">Pelanggan belum memiliki pemesanan.</div>
            @else
                <div class="pelanggan-table-scroll" tabindex="0" role="region" aria-label="Riwayat pemesanan pelanggan">
                    <table class="pelanggan-history-table">
                        <thead><tr><th scope="col">Kode Pemesanan</th><th scope="col">Paket Travel</th><th scope="col">Keberangkatan</th><th scope="col">Peserta</th><th scope="col">Total</th><th scope="col">Status Pembayaran</th></tr></thead>
                        <tbody>
                        @foreach ($p->riwayat as $r)
                            <tr>
                                <td>{{ $r->kode_pemesanan }}</td>
                                <td>{{ $r->nama_paket }}</td>
                                <td>{{ tanggal($r->jadwal_keberangkatan) }}</td>
                                <td>{{ $r->jumlah_peserta }}</td>
                                <td>{{ rupiah($r->total_pembayaran) }}</td>
                                <td>{{ $r->status_pembayaran ?? 'Belum Bayar' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
