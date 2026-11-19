@extends('layouts.app',['title'=>'Paket Travel'])
@section('content')
<div class="toolbar"><form method="get" action="{{ route('katalog.index') }}" class="search"><input class="input" name="keyword" value="{{ $keyword ?? '' }}" placeholder="Cari Paket Travel..." aria-label="Cari paket"><button class="btn" type="submit">Cari</button></form></div>
<div class="paket-grid">
@forelse($daftarPaket as $paket)
<article class="paket-card"><div class="paket-media">@include('partials.paket-photo')</div><div class="paket-body"><h2>{{ $paket->nama_paket }}</h2><p>{{ $paket->tujuan }} · {{ $paket->durasi }}</p><strong>Rp {{ number_format($paket->harga,0,',','.') }}</strong><a class="btn" href="{{ route('katalog.detail',$paket->id_paket) }}">Lihat detail</a></div></article>
@empty<div class="kosong">Paket travel belum tersedia.</div>@endforelse
</div>
@endsection
@push('head')<style>.toolbar{display:flex;justify-content:flex-end;margin-bottom:28px}.search{display:flex;gap:10px;max-width:620px;width:100%}.search .input{flex:1;min-width:0}.search .btn{flex-shrink:0}.paket-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px}.paket-card{overflow:hidden;border-radius:14px;background:var(--lime);min-height:360px}.paket-media{height:180px;background:var(--biru)}.paket-media img{width:100%;height:100%;object-fit:cover}.paket-body{padding:20px;display:grid;gap:10px}.paket-body h2{font-size:20px}.paket-body p{margin:0}.paket-body .btn{height:44px;font-size:14px;padding:0 18px}</style>@endpush