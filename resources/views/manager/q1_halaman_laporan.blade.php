@extends('layouts.app', ['title' => 'Laporan Transaksi', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/laporan.css') }}">
@endpush

@section('content')
    <section class="laporan laporan-manager" aria-label="Laporan transaksi">
        @include('partials.filter_periode', ['action' => route('manager.laporan')])

        @if (! is_null($daftarTransaksi))
            <div class="laporan-ringkasan">
                <div class="laporan-kartu"><span>Jumlah Pemesanan Lunas</span><strong>{{ $ringkasan['jumlah_lunas'] }}</strong></div>
                <div class="laporan-kartu"><span>Total Peserta</span><strong>{{ $ringkasan['total_peserta'] }}</strong></div>
                <div class="laporan-kartu laporan-kartu-uang"><span>Total Transaksi</span><strong>Rp. {{ number_format($ringkasan['total_transaksi'], 0, ',', '.') }}</strong></div>
            </div>

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
                <div class="laporan-total"><span>Total Transaksi Periode:</span><span>Rp.{{ number_format($ringkasan['total_transaksi'], 0, ',', '.') }}</span></div>
                <div class="laporan-aksi">
                    @foreach (['pdf' => 'Ekspor PDF', 'xlsx' => 'Ekspor Excel'] as $format => $label)
                        <a class="btn laporan-ekspor" href="{{ route('manager.laporan.ekspor', $periode + ['format_file' => $format]) }}">{{ $label }}</a>
                    @endforeach
                </div>
            @endif
        @else
            <div class="kosong">Pilih periode, lalu tekan Tampilkan untuk meninjau laporan transaksi.</div>
        @endif
    </section>
@endsection
