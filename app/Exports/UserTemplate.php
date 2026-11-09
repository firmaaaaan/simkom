<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplate implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Password',
            'Role',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh',
            'contoh@unisayogya.ac.id',
            'Password123',
            'admin',
        ]];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
