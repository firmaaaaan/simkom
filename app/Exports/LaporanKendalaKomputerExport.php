<?php

namespace App\Exports;

use App\Models\LaporanKendalaKomputer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanKendalaKomputerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $laporan;

    public function __construct($laporan)
    {
        $this->laporan = $laporan;
    }

    public function collection()
    {
        return $this->laporan;
    }

    public function headings(): array
    {
        return [
            'No',
            'Komputer',
            'Kode Komputer',
            'Laboratorium',
            'Nama Pelapor',
            'NPM/NIM',
            'Nama Prodi',
            'Deskripsi Kendala',
            'Status Kendala',
            'Kode Tracker',
            'Tanggal Lapor',
            'Tanggal Perbaikan',
            'Catatan Admin',
        ];
    }

    public function map($laporan): array
    {
        static $no = 1;

        return [
            $no++,
            $laporan->komputer->nama_komputer ?? '-',
            $laporan->komputer->kode_komputer ?? '-',
            $laporan->komputer->laboratorium->nama_laboratorium ?? '-',
            $laporan->nama_pelapor,
            $laporan->npm_nim,
            $laporan->nama_prodi ?? '-',
            $laporan->deskripsi_kendala,
            ucfirst($laporan->status_kendala),
            $laporan->kode_tracker ?? '-',
            $laporan->tanggal_lapor ? $laporan->tanggal_lapor->format('d F Y') : '-',
            $laporan->tanggal_perbaikan ? $laporan->tanggal_perbaikan->format('d F Y') : '-',
            $laporan->catatan_admin ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
