<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#16202E}
        h1{font-size:18px;margin:0 0 4px;color:#1450A8}
        .periode{margin:0 0 12px;color:#5B6678}
        .ringkasan{width:100%;margin-bottom:12px;border-collapse:collapse}
        .ringkasan td{background:#1E5FCB;color:#fff;padding:8px 10px;border:3px solid #fff}
        .ringkasan b{display:block;font-size:14px}
        table.data{width:100%;border-collapse:collapse}
        .data th{background:#D4F835;text-align:left;padding:6px;border:1px solid #C5D0E0}
        .data td{padding:6px;border:1px solid #E3E8F0}
        .angka{text-align:right}
        tfoot td{font-weight:bold;background:#E9FB9A}
    </style>
</head>
<body>
<h1>Laporan Transaksi Travel Tour</h1>
<p class="periode">Periode {{ $periode['jenis'] }}: {{ tanggal($periode['periode_awal']) }} – {{ tanggal($periode['periode_akhir']) }} · Dicetak {{ tanggal(now()) }}</p>
<table class="ringkasan">
    <tr>
        <td>Jumlah Pemesanan Lunas<b>{{ $ringkasan['jumlah_lunas'] }}</b></td>
        <td>Total Peserta<b>{{ $ringkasan['total_peserta'] }}</b></td>
        <td>Total Transaksi<b>{{ rupiah($ringkasan['total_transaksi']) }}</b></td>
    </tr>
</table>
<table class="data">
    <thead>
    <tr>
        <th>Tanggal</th><th>Kode Pemesanan</th><th>Customer</th><th>Paket Wisata</th>
        <th class="angka">Peserta</th><th>Metode</th><th class="angka">Total Bayar</th><th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($daftarTransaksi as $t)
        <tr>
            <td>{{ tanggal($t->tanggal_bayar) }}</td>
            <td>{{ $t->kode_pemesanan }}</td>
            <td>{{ $t->nama_customer }}</td>
            <td>{{ $t->nama_paket }}</td>
            <td class="angka">{{ $t->jumlah_peserta }}</td>
            <td>{{ $t->metode_pembayaran ?? 'Transfer Bank' }}</td>
            <td class="angka">{{ rupiah($t->nominal) }}</td>
            <td>{{ $t->status_pembayaran }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr><td colspan="6">Total Transaksi Periode (Lunas)</td><td class="angka">{{ rupiah($ringkasan['total_transaksi']) }}</td><td></td></tr>
    </tfoot>
</table>
</body>
</html>
