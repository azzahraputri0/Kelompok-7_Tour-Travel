{{-- Filter periode laporan: dipakai J1 (Staff) dan Q1 (Manager). Tanggal tampil dd/mm/yyyy seperti Figma. --}}
@php
    $tampil = fn ($iso) => $iso ? \Illuminate\Support\Carbon::parse($iso)->format('d/m/Y') : '';
@endphp
<form class="laporan-filter" method="get" action="{{ $action }}" id="form-periode">
    <div class="field">
        <label for="jenis">Jenis Periode</label>
        <select class="input" id="jenis" name="jenis" required>
            @foreach (['Harian', 'Mingguan', 'Bulanan'] as $j)
                <option value="{{ $j }}" @selected(($periode['jenis'] ?? '') === $j)>{{ $j }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="periode_awal">Tanggal Awal</label>
        <input class="input" type="text" inputmode="numeric" id="periode_awal" name="periode_awal" placeholder="dd/mm/yyyy"
               pattern="\d{1,2}/\d{1,2}/\d{4}" title="Format tanggal dd/mm/yyyy" value="{{ $tampil($periode['periode_awal'] ?? null) }}" required>
    </div>
    <div class="field">
        <label for="periode_akhir">Tanggal Akhir</label>
        <input class="input" type="text" inputmode="numeric" id="periode_akhir" name="periode_akhir" placeholder="dd/mm/yyyy"
               pattern="\d{1,2}/\d{1,2}/\d{4}" title="Format tanggal dd/mm/yyyy" value="{{ $tampil($periode['periode_akhir'] ?? null) }}" required>
    </div>
    <button type="submit" class="btn">Tampilkan</button>
</form>
@push('scripts')
<script>
    // Memilih jenis periode mengisi tanggal otomatis; tanggal tetap bisa diubah manual.
    document.getElementById('jenis').addEventListener('change', function () {
        var akhir = new Date(), awal = new Date();
        if (this.value === 'Mingguan') awal.setDate(akhir.getDate() - 6);
        if (this.value === 'Bulanan') awal = new Date(akhir.getFullYear(), akhir.getMonth(), 1);
        var f = function (d) { return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear(); };
        document.getElementById('periode_awal').value = f(awal);
        document.getElementById('periode_akhir').value = f(akhir);
    });
</script>
@endpush
