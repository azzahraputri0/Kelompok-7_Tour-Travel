@php
$photo = $paket->foto_paket ?? '';
$photoUrl = str_starts_with($photo, 'paket/') ? route('katalog.foto', $paket->id_paket) : null;
if (preg_match('~^assets/img/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$~D', $photo) && is_file(public_path($photo))) $photoUrl = asset($photo);
@endphp
@if($photoUrl)<img src="{{ $photoUrl }}" alt="{{ $paket->nama_paket }}">@else<span>Foto belum tersedia</span>@endif
