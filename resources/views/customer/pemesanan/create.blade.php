@extends('layouts.app',['title'=>'Pesan Paket '.$paket->nama_paket])
@section('content')
<form class="booking-layout" method="post" action="{{ route('customer.pesanan.penumpang',$paket->id_paket) }}">@csrf
<section class="booking-form">
<div class="travel-field"><label for="jadwal">Jadwal Keberangkatan</label><input class="input" id="jadwal" value="{{ \Carbon\Carbon::parse($paket->jadwal_keberangkatan)->format('d/m/Y') }}" readonly><input type="hidden" name="jadwal_keberangkatan" value="{{ $paket->jadwal_keberangkatan }}"></div>
<div class="travel-field"><label for="jumlah">Jumlah Peserta</label><input class="input" type="number" name="jumlah_peserta" id="jumlah" min="1" max="{{ min(100,$paket->sisa_kuota) }}" value="{{ old('jumlah_peserta',1) }}" required><small>Tersedia {{ $paket->sisa_kuota }} tempat.</small></div>
@foreach(['nama'=>['Nama Lengkap',auth()->user()->nama_lengkap],'telepon'=>['No. HP',auth()->user()->nomor_telepon],'email'=>['Email',auth()->user()->email]] as $key=>[$label,$value])
<div class="travel-field"><label for="pemesan-{{ $key }}">{{ $label }}</label><input class="input" id="pemesan-{{ $key }}" value="{{ $value }}" readonly></div>
@endforeach
<div class="travel-actions"><a class="btn secondary" href="{{ route('katalog.detail',$paket->id_paket) }}">Kembali</a>@if($paket->sisa_kuota>0)<button class="btn" type="submit">Lanjutkan</button>@endif</div>
</section>
<aside class="booking-invoice"><h2>Ringkasan Pesanan</h2><div class="invoice-card" data-price="{{ $paket->harga }}" data-admin="{{ $paket->biaya_admin ?? 0 }}"><dl><dt>{{ $paket->nama_paket }} × <span data-quantity>1</span></dt><dd data-subtotal>Rp {{ number_format($paket->harga,0,',','.') }}</dd><dt>Biaya Admin</dt><dd>Rp {{ number_format($paket->biaya_admin ?? 0,0,',','.') }}</dd><dt>Total</dt><dd data-total>Rp {{ number_format($paket->harga+($paket->biaya_admin ?? 0),0,',','.') }}</dd></dl><p class="invoice-note">Ini ringkasan biaya, bukan invoice. Invoice dan instruksi pembayaran tersedia setelah data penumpang disimpan dan pemesanan dibuat.</p></div></aside>
</form>
@endsection
