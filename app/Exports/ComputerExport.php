<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ComputerExport extends BaseExport implements WithHeadings, WithMapping
{
    public function headings(): array
    {
        return [
            'Kode Komputer',
            'Laboratorium',
            'Status',
            'Alasan Maintenance',
            'Hardware',
            'Software',
            'Keterangan',
            'Ditambahkan',
        ];
    }

    public function map($computer): array
    {
        return [
            $computer->code,
            $computer->laboratory->name ?? '-',
            $computer->status,
            $computer->maintenance_reason,
            $computer->hardware->pluck('name')->implode(', ') ?: '-',
            $computer->software->pluck('name')->implode(', ') ?: '-',
            $computer->description,
            $computer->created_at?->format('d/m/Y H:i'),
        ];
    }
}
