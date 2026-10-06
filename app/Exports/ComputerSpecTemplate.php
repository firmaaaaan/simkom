<?php

namespace App\Exports;

use App\Models\Hardware;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import spesifikasi komputer. Judul kolomnya mengikuti daftar
 * kategori pada form hardware (Hardware::CATEGORIES). Selain "Kode Komputer",
 * "Laboratorium" dan "Keterangan", semua kolom lain diperlakukan sebagai
 * kategori spesifikasi, sehingga pengguna bebas menambah kolom sesuai file
 * mereka sendiri.
 */
class ComputerSpecTemplate implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return array_merge(
            ['Kode Komputer', 'Laboratorium'],
            Hardware::CATEGORIES,
            ['Keterangan'],
        );
    }

    public function collection(): Collection
    {
        $specs = array_fill_keys(Hardware::CATEGORIES, '');
        $specs['Processor'] = 'Intel Core i5-12400';
        $specs['RAM'] = '8GB DDR4';
        $specs['Storage'] = 'SSD 512GB';
        $specs['Monitor'] = '24 Inch';

        return collect([
            array_merge(
                ['Contoh', 'Lab Komputer 1'],
                array_values($specs),
                [''],
            ),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
