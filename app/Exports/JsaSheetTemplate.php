<?php

namespace App\Exports;

use App\Models\JobSafetyAnalysis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "JSA" pada template JSA. */
class JsaSheetTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'JSA';
    }

    public function headings(): array
    {
        return [
            'Kode', 'Nama Pekerja', 'Nama Pekerjaan', 'Tanggal Pekerjaan', 'Tempat',
            'APD', 'APD Lain',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh',
            'Andi Pratama',
            'Penggantian Thermal Paste PC',
            '2026-11-05',
            'Lab Komputer 1',
            'Helm, Sarung Tangan, Kacamata Pengaman',
            'Masker N95',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings()));

        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getColumnDimension('B')->setWidth(24);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('F')->setWidth(36);

        return [];
    }
}
