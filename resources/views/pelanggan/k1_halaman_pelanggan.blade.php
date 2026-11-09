@extends('layouts.app', ['title' => 'Data Pelanggan', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/pelanggan.css') }}">
@endpush

@section('content')
    <section class="pelanggan-list" aria-labelledby="judul-pelanggan">
        <h1 id="judul-pelanggan" class="pelanggan-title">Data Pelanggan</h1>
        <form class="pelanggan-search" method="get" action="{{ route('pelanggan.cari') }}" role="search">
            <label class="pelanggan-sr" for="keyword">Cari nama / nomor HP</label>
            <input id="keyword" name="keyword" type="search" value="{{ $keyword }}" maxlength="50" placeholder="Cari nama / no.HP....">
            <button class="pelanggan-sr pelanggan-search-submit" type="submit">Cari</button>
        </form>
        @if ($keyword !== '')
            <a class="pelanggan-reset" href="{{ route('pelanggan.index') }}">Reset pencarian</a>
        @endif

        @if ($pesan ?? null)
            <div class="kosong pelanggan-empty">{{ $pesan }}</div>
        @elseif ($dataPelanggan->isEmpty())
            <div class="kosong pelanggan-empty">Belum ada data pelanggan.</div>
        @else
            <div class="pelanggan-table-scroll" tabindex="0" role="region" aria-label="Daftar pelanggan">
                <table class="pelanggan-table">
                    <colgroup><col class="pelanggan-col-name"><col class="pelanggan-col-phone"><col class="pelanggan-col-action"></colgroup>
                    <thead><tr><th scope="col">Nama</th><th scope="col">No. HP</th><th scope="col">Detail</th></tr></thead>
                    <tbody>
                    @foreach ($dataPelanggan as $p)
                        <tr>
                            <td>{{ $p->nama_lengkap }}<span class="pelanggan-sr">, Status Data: {{ $p->status_data }}</span></td>
                            <td>{{ $p->nomor_telepon }}</td>
                            <td><a class="pelanggan-detail-link" href="{{ route('pelanggan.detail', $p->id_pengguna) }}" aria-label="Detail {{ $p->nama_lengkap }}">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
