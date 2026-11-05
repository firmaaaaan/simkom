<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Temuan" pada template laporan inspeksi K3 (rujuk Kode Laporan). */
class K3FindingTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Temuan';
    }

    public function headings(): array
    {
        return [
            'Kode Laporan', 'Temuan', 'Dokumentasi',
            'Saran Perbaikan', 'Pelaksana', 'Target', 'Catatan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh',
                'Sebagian APAR melewati masa berlaku isi ulang.',
                'Foto APAR di koridor lantai 1',
                'Isi ulang APAR pada vendor terpercaya.',
                'Petugas K3',
                '2026-12-01',
                '',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(38);

        return [];
    }
}
