<?php

namespace App\Exports;

use App\Models\K3InspectionReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Sheet "Checklist" pada template laporan inspeksi K3: hasil periksa
 * item 1-33 (rujuk Kode Laporan). Item yang tidak diisi memakai
 * checklist default saat import.
 */
class K3ChecklistTemplate implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Checklist';
    }

    public function headings(): array
    {
        return ['Kode Laporan', 'No Item', 'Hasil', 'Catatan'];
    }

    public function array(): array
    {
        $rows = [];

        // Contoh: lima item pertama + satu item NA agar pengguna paham polanya.
        foreach ([1, 2, 3, 4, 5, 6] as $index => $no) {
            $rows[] = [
                'Contoh',
                $no,
                $no === 6 ? 'NA' : 'Ya',
                $index === 0 ? 'Terkait poster komitmen K3' : '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
