<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Sheet "Peralatan" pada template laporan kerusakan (rujuk Kode Laporan). */
class DamageEquipmentTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Peralatan';
    }

    public function headings(): array
    {
        return ['Kode Laporan', 'Nama Peralatan', 'Merk', 'No Inventaris', 'Jumlah', 'Keterangan'];
    }

    public function array(): array
    {
        return [
            ['Contoh', 'Monitor LED 24 inch', 'LG', 'INV-2024-011', '1', 'Pecah'],
            ['Contoh', 'Keyboard', 'Logitech', 'INV-2024-012', '1', 'Retak'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
