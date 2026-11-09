<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class LaporanTransaksiMail extends Mailable
{
    public function __construct(public Collection $daftarTransaksi, public array $periode, public array $ringkasan) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Laporan Transaksi '.tanggal($this->periode['periode_awal']).' - '.tanggal($this->periode['periode_akhir']));
    }

    public function content(): Content
    {
        return new Content(view: 'mail.laporan_transaksi');
    }

    public function attachments(): array
    {
        $data = ['daftarTransaksi' => $this->daftarTransaksi, 'periode' => $this->periode, 'ringkasan' => $this->ringkasan];

        return [
            Attachment::fromData(fn () => Pdf::loadView('reports.pdf', $data)->setPaper('a4', 'landscape')->output(), 'laporan-transaksi.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
