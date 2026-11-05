<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class K3DocumentTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Kode', 'Judul', 'Kategori', 'Nomor Dokumen',
            'Tanggal Berlaku', 'Tanggal Revisi', 'Keterangan',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh', 'SOP Penggunaan APAR', 'sop', 'SOP/K3/001',
            '2026-11-05', null, 'Standar operasional penggunaan APAR',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
