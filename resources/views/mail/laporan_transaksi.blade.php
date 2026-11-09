<!doctype html>
<html lang="id">
<body style="font-family:Arial,sans-serif;color:#16202E">
<p>Yth. Manager,</p>
<p>Berikut laporan transaksi periode {{ $periode['jenis'] }} {{ tanggal($periode['periode_awal']) }} – {{ tanggal($periode['periode_akhir']) }}:</p>
<ul>
    <li>Jumlah transaksi: {{ $daftarTransaksi->count() }}</li>
    <li>Jumlah pemesanan lunas: {{ $ringkasan['jumlah_lunas'] }}</li>
    <li>Total peserta: {{ $ringkasan['total_peserta'] }}</li>
    <li>Total transaksi (lunas): {{ rupiah($ringkasan['total_transaksi']) }}</li>
</ul>
<p>Rincian lengkap terlampir dalam file PDF.</p>
<p>Salam,<br>{{ auth()->user()->nama_lengkap ?? 'Staff' }} – Travel Tour</p>
</body>
</html>
