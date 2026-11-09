@extends('layouts.app', ['title' => 'Laporan Transaksi'])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/laporan.css') }}">
@endpush

@section('content')
    <section class="laporan laporan-staff" aria-label="Laporan transaksi">
        @include('partials.filter_periode', ['action' => route('staff.laporan')])

        @if (! is_null($daftarTransaksi))
            @if ($pesan ?? null)
                <div class="kosong">{{ $pesan }}</div>
            @else
                <div class="laporan-scroll" role="region" aria-label="Daftar transaksi" tabindex="0">
                    <table class="laporan-tabel">
                        <colgroup><col><col><col><col><col><col><col></colgroup>
                        <thead>
                            <tr><th scope="col">Kode</th><th scope="col">Customer</th><th scope="col">Paket<br>Wisata</th><th scope="col">Peserta</th><th scope="col">Metode</th><th scope="col">Total<br>Bayar</th><th scope="col">Tanggal</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarTransaksi as $t)
                                <tr>
                                    <td>{{ $t->kode_pemesanan }}</td>
                                    <td>{{ $t->nama_customer }}</td>
                                    <td>{{ $t->nama_paket }}</td>
                                    <td>{{ $t->jumlah_peserta }}</td>
                                    <td>{{ $t->metode_pembayaran ?? 'Transfer Bank' }}</td>
                                    <td>Rp. {{ number_format($t->nominal, 0, ',', '.') }}</td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal_bayar)->format('j/n/y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="laporan-total"><span>Total Transaksi Periode:</span><span>Rp.{{ number_format($daftarTransaksi->sum('nominal'), 0, ',', '.') }}</span></div>
                <form class="laporan-aksi" method="post" action="{{ route('staff.laporan.kirim') }}">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $periode['jenis'] }}">
                    <input type="hidden" name="periode_awal" value="{{ $periode['periode_awal'] }}">
                    <input type="hidden" name="periode_akhir" value="{{ $periode['periode_akhir'] }}">
                    <button type="submit" class="btn">Kirim Laporan</button>
                </form>
            @endif
        @else
            <div class="kosong">Pilih periode, lalu tekan Tampilkan untuk melihat laporan transaksi.</div>
        @endif
    </section>
@endsection
