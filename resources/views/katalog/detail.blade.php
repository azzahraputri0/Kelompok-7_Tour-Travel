@extends('layouts.app',['title'=>'Detail Paket Travel'])
@section('content')
<a class="btn" href="{{ route('katalog.index') }}">Kembali</a>
<article class="travel-panel"><div class="package-detail-photo">@include('partials.paket-photo')</div><h2>{{ $paket->nama_paket }}</h2><p>{{ $paket->tujuan }} · {{ $paket->durasi }}</p><p>{{ $paket->deskripsi }}</p><h3>Fasilitas</h3><p style="white-space:pre-line">{{ filled($paket->fasilitas) ? $paket->fasilitas : 'Belum dicantumkan' }}</p><p>Keberangkatan: {{ \Carbon\Carbon::parse($paket->jadwal_keberangkatan)->format('d/m/Y') }}</p><p>Sisa kuota: {{ $paket->sisa_kuota }}</p><h3>Rp {{ number_format($paket->harga,0,',','.') }} / peserta</h3><p>Biaya admin: Rp {{ number_format($paket->biaya_admin ?? 0,0,',','.') }}</p>
@if($paket->sisa_kuota > 0 && \Carbon\Carbon::parse($paket->jadwal_keberangkatan)->endOfDay()->isFuture())
@guest<a class="btn" href="{{ route('login') }}">Masuk untuk memesan</a>@else @if(auth()->user()->role === 'Customer')<a class="btn" href="{{ route('customer.pesanan.create',$paket->id_paket) }}">Pesan Paket</a>@endif @endguest
@else<p>Paket tidak tersedia untuk dipesan.</p>@endif</article>
@endsection
