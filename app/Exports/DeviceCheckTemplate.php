<?php

namespace App\Exports;

use App\Models\DeviceCheck;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import pengecekan perangkat. Judul kolom item diambil dari
 * DeviceCheck::itemColumns() (label bergrup digabung, mis. "Kabel Power PC")
 * sehingga otomatis mengikuti perubahan item. Baris contoh memakai kode
 * "Contoh" agar otomatis dilewati saat diunggah apa adanya.
 */
class DeviceCheckTemplate implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        $headings = ['No', 'Kode Komputer'];

        foreach (DeviceCheck::itemColumns() as $column) {
            $headings[] = isset($column['group'])
                ? $column['group'].' '.$column['label']
                : $column['label'];
        }

        return $headings;
    }

    public function collection(): Collection
    {
        $itemCount = DeviceCheck::itemColumnCount();

        return collect([
            array_merge([1, 'Contoh'], array_fill(0, $itemCount, '✓')),
            array_merge([2, 'Contoh'], array_fill(0, $itemCount, '')),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
