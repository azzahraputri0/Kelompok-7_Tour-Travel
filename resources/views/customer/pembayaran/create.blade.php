@extends('layouts.app',['title'=>'Pembayaran'])
@section('content')<div class="travel-steps"><span>1. Pilih perjalanan</span><span>2. Data penumpang</span><strong>3. Pembayaran</strong></div>@include('partials.order-detail')
<section class="travel-panel"><h2>QRIS Manual</h2>
@if($qrisImage)
<img src="{{ $qrisImage }}" alt="QRIS resmi pengelola" width="280">
<ol>
<li>Pindai QRIS resmi pengelola menggunakan aplikasi pembayaran Anda sendiri. Periksa nama merchant sebelum membayar; jika berbeda, hentikan dan hubungi pengelola.</li>
<li>Untuk QR statis, masukkan nominal tepat sesuai total pesanan: <strong>{{ rupiah($detailPemesanan->total_pembayaran) }}</strong>. Pastikan nominal dan penerima benar di aplikasi pembayaran.</li>
<li>Simpan bukti transaksi, lalu unggah di bawah untuk verifikasi manual oleh staff. Staff harus mencocokkan penerimaan dana pada akun merchant.</li>
</ol>
@else
<div class="travel-notice" role="alert"><strong>DEMO — QRIS resmi belum dikonfigurasi atau aset tidak tersedia.</strong><br>Jangan melakukan pembayaran. Hubungi pengelola untuk memperoleh QRIS resmi. Unggah hanya bukti uji untuk demonstrasi; ini bukan transaksi atau pelunasan sungguhan.</div>
@endif
<p>Unggah bukti tidak otomatis melunasi pesanan. Status menunggu verifikasi manual oleh staff; aplikasi ini tidak menggunakan payment gateway atau konfirmasi pembayaran otomatis.</p>
@if($detailPemesanan->status==='Belum Terbayar')<form method="post" enctype="multipart/form-data" action="{{ route('customer.pembayaran.store',$detailPemesanan->id_pemesanan) }}">@csrf<div class="travel-field"><label for="bukti">{{ $qrisImage ? 'Upload Bukti Pembayaran QRIS' : 'Upload Bukti Uji DEMO (bukan pelunasan)' }}</label><input class="input" id="bukti" name="bukti_pembayaran" type="file" accept="image/jpeg,image/png,application/pdf" required><small>JPG, PNG, atau PDF. Maksimal 4 MB. Bukti akan diperiksa oleh staff.</small></div><div class="travel-actions"><a class="btn secondary" href="{{ route('customer.pesanan.detail',$detailPemesanan->id_pemesanan) }}">Kembali</a><button class="btn">{{ $qrisImage ? 'Kirim Bukti Pembayaran' : 'Kirim Bukti Uji DEMO' }}</button></div></form>@else<p>Pemesanan ini tidak dapat menerima bukti pembayaran baru.</p>@endif</section>@endsection
