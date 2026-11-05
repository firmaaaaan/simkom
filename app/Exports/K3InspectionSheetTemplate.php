<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Laporan" pada template laporan inspeksi K3 (Form 05). */
class K3InspectionSheetTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Laporan';
    }

    public function headings(): array
    {
        return [
            'Kode', 'Hari', 'Tanggal BA', 'Bulan BA', 'Tahun BA',
            'Bulan Inspeksi', 'Lokasi', 'Pemeriksa Nama', 'Pemeriksa Jabatan',
        ];
    }

    public function array(): array
    {
        return [
            ['Contoh', 'Rabu', '2026-11-05', 'November', '2026', 'November', 'Lab Komputer 1', 'Asih Puji Utami, S.KM., M.Kes.', 'Koordinator K3 Laboratorium'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getColumnDimension('H')->setWidth(28);
        $sheet->getColumnDimension('I')->setWidth(28);

        return [];
    }
}
