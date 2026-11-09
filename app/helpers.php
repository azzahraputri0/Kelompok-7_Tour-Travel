<?php

use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    function rupiah($nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    function tanggal($tanggal): string
    {
        return $tanggal ? Carbon::parse($tanggal)->locale('id')->translatedFormat('d M Y') : '-';
    }
}
