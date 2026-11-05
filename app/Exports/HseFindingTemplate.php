<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Temuan" pada template inspeksi HSE (rujuk Kode HSE). */
class HseFindingTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Temuan';
    }

    public function headings(): array
    {
        return [
            'Kode HSE', 'Lokasi', 'Temuan',
            'Unsafe Condition', 'Unsafe Action',
            'Tanggal Kejadian', 'Tindakan Perbaikan', 'Tanggal Perbaikan', 'PIC',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Contoh', 'Lab Komputer 1', 'Kabel listrik tidak ber-ground.',
                'Ya', 'Tidak', '2026-11-05', 'Penggantian kabel dan pemasangan ground.', '2026-11-12', 'Andi Pratama',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(34);
        $sheet->getColumnDimension('G')->setWidth(38);

        return [];
    }
}
