@extends('layouts.app', ['title' => 'Dashboard', 'sembunyikanJudul' => true])

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <script src="{{ asset('assets/chart.umd.min.js') }}" defer></script>
@endpush

@section('content')
    <div class="manager-dashboard">
        <h1 class="dashboard-sr">Dashboard Manager</h1>
        <p class="dashboard-sr">Total Driver: {{ $totalDriver }}</p>
        <div class="dashboard-metrics manager-metrics">
            <div class="dashboard-metric"><h2>Total Transaksi</h2><strong>{{ $totalTransaksi }}</strong></div>
            <div class="dashboard-metric dashboard-revenue"><h2>Pendapatan</h2><strong>Rp. {{ number_format($pendapatan, 0, ',', '.') }}</strong></div>
            <div class="dashboard-metric"><h2>Pesanan Lunas</h2><strong>{{ $pesananLunas }}</strong></div>
        </div>

        <figure class="dashboard-chart">
            <div class="dashboard-chart-card">
                <div class="dashboard-chart-canvas">
                    <canvas id="grafik-pendapatan" aria-label="Grafik pendapatan bulanan, enam bulan terakhir" role="img">
                        @foreach ($grafik as $bulan)
                            {{ $bulan['label'] }}: {{ rupiah($bulan['nilai']) }}.
                        @endforeach
                    </canvas>
                </div>
            </div>
            <figcaption>Grafik rekap transaksi bulanan</figcaption>
        </figure>
    </div>
@endsection

@push('scripts')
<script>
    window.addEventListener('load', function () {
        var data = @json($grafik);
        new Chart(document.getElementById('grafik-pendapatan'), {
            type: 'bar',
            data: {
                labels: data.map(function (d) { return d.label; }),
                datasets: [{ label: 'Pendapatan', data: data.map(function (d) { return d.nilai; }), backgroundColor: '#2E3F59', borderRadius: 0, barPercentage: 0.76, categoryPercentage: 0.9 }]
            },
            options: {
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return 'Rp ' + c.raw.toLocaleString('id-ID'); } } }
                },
                scales: {
                    x: { grid: { display: false }, border: { color: '#2E3F59' }, ticks: { color: '#2E3F59', font: { family: 'Arial', size: 12 }, maxRotation: 0 } },
                    y: { beginAtZero: true, border: { display: false }, grid: { color: '#A6ADB5', tickLength: 0 }, ticks: { count: 5, padding: 14, color: '#2E3F59', font: { family: 'Arial', size: 18, weight: 'bold' }, callback: function (v) { return (v / 1000000).toLocaleString('id-ID') + ' jt'; } } }
                }
            }
        });
    });
</script>
@endpush
