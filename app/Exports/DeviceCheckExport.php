<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeviceCheckExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'academicYear'])
            ->withCount([
                'items',
                'items as checked_items_count' => fn ($query) => $query->where('is_checked', true),
                'items as computers_count' => fn ($query) => $query->select(DB::raw('count(distinct computer_id)')),
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Laboratorium',
            'Tahun Ajaran',
            'Petugas',
            'Jumlah Komputer',
            'Sel Berfungsi',
            'Sel Perlu Perhatian',
            'Total Sel',
            'Persentase Berfungsi',
            'Catatan',
        ];
    }

    public function map($check): array
    {
        $total = (int) $check->items_count;
        $checked = (int) $check->checked_items_count;

        return [
            $check->check_date?->format('d/m/Y'),
            $check->laboratory->name ?? '-',
            $check->academicYear->name ?? '-',
            $check->officer_name ?: '-',
            $check->computers_count,
            $checked,
            $total - $checked,
            $total,
            $total > 0 ? round($checked / $total * 100, 1) . '%' : '-',
            $check->notes,
        ];
    }
}
