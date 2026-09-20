<?php

namespace App\Exports;

use App\Models\Component;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComponentExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Component::all();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Kategori', 'Merk', 'Model', 'Jumlah', 'Status', 'Keterangan'];
    }

    public function map($component): array
    {
        return [
            $component->code,
            $component->name,
            $component->category,
            $component->brand,
            $component->model,
            $component->quantity,
            $component->status,
            $component->description,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
