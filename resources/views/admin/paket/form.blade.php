@extends('layouts.app',['title'=>($paket ? 'Form Edit Paket Wisata' : 'Form Tambah Paket Wisata')])
@push('head')<link rel="stylesheet" href="{{ asset('assets/admin-master.css') }}">@endpush
@section('content')
@php
$photo = $paket->foto_paket ?? '';
$photoUrl = str_starts_with($photo, 'paket/') ? route('katalog.foto',$paket->id_paket) : (str_starts_with($photo, 'assets/img/') ? asset($photo) : asset('assets/img/paket-bali.jpg'));
$fields = ['nama_paket'=>['Nama Paket','text','full'],'tujuan'=>['Tujuan','text',''],'durasi'=>['Durasi','text',''],'harga'=>['Harga (Rp)','number',''],'kuota'=>['Kuota','number',''],'jadwal_keberangkatan'=>['Jadwal Keberangkatan','date','full'],'deskripsi'=>['Deskripsi','textarea','full']];
@endphp
<form class="travel-form package-master-form" method="post" enctype="multipart/form-data" action="{{ $paket ? route('admin.paket.update',$paket->id_paket) : route('admin.paket.store') }}">
@csrf @if($paket) @method('PUT') @endif
<div class="package-master-grid">
<div class="travel-fields package-master-fields">
@foreach($fields as $name=>[$label,$type,$width])
<div class="travel-field {{ $width }}"><label for="{{ $name }}">{{ $label }}</label>
@if($type==='textarea')<textarea class="input" id="{{ $name }}" name="{{ $name }}">{{ old($name,$paket->$name ?? '') }}</textarea>
@else<input class="input" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name,$paket->$name ?? '') }}" required @if($type==='number') min="1" @endif>@endif
@error($name)<span class="travel-error">{{ $message }}</span>@enderror
</div>
@endforeach
</div>
<div class="package-photo-field"><label for="foto_paket">Foto Paket</label><div class="package-photo-panel">
<img id="package-photo-preview" src="{{ $photoUrl }}" alt="{{ $paket->nama_paket ?? 'Pratinjau foto paket wisata' }}">
<input id="foto_paket" name="foto_paket" type="file" accept="image/jpeg,image/png,image/webp">
@error('foto_paket')<span class="travel-error">{{ $message }}</span>@enderror
</div></div>
</div>
<div class="travel-fields package-extra-fields">
<div class="travel-field"><label for="fasilitas">Fasilitas</label><textarea class="input" id="fasilitas" name="fasilitas">{{ old('fasilitas',$paket->fasilitas ?? '') }}</textarea>@error('fasilitas')<span class="travel-error">{{ $message }}</span>@enderror</div>
<div class="travel-field"><label for="biaya_admin">Biaya Admin</label><input class="input" id="biaya_admin" name="biaya_admin" type="number" min="0" value="{{ old('biaya_admin',$paket->biaya_admin ?? 0) }}">@error('biaya_admin')<span class="travel-error">{{ $message }}</span>@enderror</div>
</div>
<div class="travel-actions"><a class="btn secondary" href="{{ route('admin.paket.index') }}">Batal</a><button class="btn" type="submit">Simpan</button></div>
</form>
@endsection
@push('scripts')<script>
(() => {
 const input = document.getElementById('foto_paket'), preview = document.getElementById('package-photo-preview'), original = preview.src;
 let objectUrl;
 input.addEventListener('change', () => {
  if (objectUrl) URL.revokeObjectURL(objectUrl);
  const file = input.files[0];
  preview.src = file && ['image/jpeg','image/png','image/webp'].includes(file.type) ? (objectUrl = URL.createObjectURL(file)) : original;
 });
})();
</script>@endpush
