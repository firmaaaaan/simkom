<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BoxExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query->with('components')->withCount('components')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Box',
            'Nama Box',
            'Lokasi',
            'Jenis Komponen',
            'Total Unit Komponen',
            'Isi Komponen',
            'Catatan',
            'Keterangan',
        ];
    }

    public function map($box): array
    {
        return [
            $box->code,
            $box->name,
            $box->location,
            $box->components_count,
            $box->components->sum(fn ($component) => (int) $component->pivot->quantity),
            $box->components
                ->map(fn ($component) => $component->name . ' (' . $component->pivot->quantity . ' unit)')
                ->implode(', ') ?: '-',
            $box->notes,
            $box->description,
        ];
    }
}
