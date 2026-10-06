<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import kartu kendali historis. Baris contoh memakai kode "Contoh"
 * agar otomatis dilewati saat diunggah apa adanya.
 */
class ComputerCheckTemplate implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['No', 'Kode Komputer', 'Baik', 'Tidak', 'Keterangan', 'PJ'];
    }

    public function collection(): Collection
    {
        return collect([
            [1, 'Contoh', '✓', '', 'Semua fungsi berjalan normal', 'Firmansyah'],
            [2, 'Contoh', '', '✓', 'Perlu perbaikan, mis. layar retak', 'Firmansyah'],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
