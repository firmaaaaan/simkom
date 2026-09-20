<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaboratoryExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query->withCount('computers')->get();
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama Laboratorium',
            'Lokasi',
            'Kapasitas',
            'Status',
            'Jumlah Komputer',
            'Keterangan',
        ];
    }

    public function map($laboratory): array
    {
        return [
            $laboratory->code,
            $laboratory->name,
            $laboratory->location,
            $laboratory->capacity,
            $laboratory->status,
            $laboratory->computers_count,
            $laboratory->description,
        ];
    }
}
