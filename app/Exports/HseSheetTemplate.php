<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "HSE" pada template inspeksi HSE. */
class HseSheetTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'HSE';
    }

    public function headings(): array
    {
        return ['Kode', 'Lokasi', 'Diperiksa Oleh', 'Tanggal Inspeksi'];
    }

    public function array(): array
    {
        return [
            ['Contoh', 'Lab Komputer 1', 'Petugas K3', '2026-11-05'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
