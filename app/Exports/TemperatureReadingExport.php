<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TemperatureReadingExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'recordedBy'])
            ->latest('recorded_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Ruangan',
            'Kode',
            'Waktu',
            'Suhu (°C)',
            'Status',
            'Catatan',
            'Diinput Oleh',
        ];
    }

    public function map($reading): array
    {
        return [
            $reading->laboratory->name ?? '-',
            $reading->laboratory->code ?? '-',
            $reading->recorded_at?->format('d/m/Y H:i') ?? '-',
            number_format((float) $reading->temperature, 1, ',', '.'),
            $reading->status() ?? '-',
            $reading->notes ?? '-',
            $reading->recordedBy->name ?? '-',
        ];
    }
}
