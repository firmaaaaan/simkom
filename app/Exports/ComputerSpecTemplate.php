<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import spesifikasi komputer. Judul kolomnya fleksibel: selain
 * "Kode Komputer" dan "Laboratorium", semua kolom lain diperlakukan sebagai
 * kategori spesifikasi, sehingga pengguna bebas menambah kolom sesuai file
 * mereka sendiri.
 */
class ComputerSpecTemplate implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['Kode Komputer', 'Laboratorium', 'Processor', 'RAM', 'Storage', 'Monitor'];
    }

    public function collection(): Collection
    {
        return collect([
            ['Contoh', 'Lab Komputer 1', 'Intel Core i5-12400', '8GB DDR4', 'SSD 512GB', '24 Inch'],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
