<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LabUsageExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'validatedBy'])
            ->latest('checked_in_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Prodi',
            'Keperluan',
            'Laboratorium',
            'Hari',
            'Check-in',
            'Durasi',
            'Status',
            'Divalidasi',
            'Oleh',
            'Catatan Keluar',
        ];
    }

    public function map($usage): array
    {
        return [
            $usage->user_name,
            $usage->user_prodi,
            $usage->purpose,
            $usage->laboratory->name ?? '-',
            $usage->day,
            $usage->checked_in_at?->format('d/m/Y H:i') ?? '-',
            $usage->duration ?? '-',
            $usage->status,
            $usage->validated_at?->format('d/m/Y H:i') ?? '-',
            $usage->validatedBy->name ?? '-',
            $usage->exit_note ?? '-',
        ];
    }
}
