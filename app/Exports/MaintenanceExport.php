<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaintenanceExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'academicYear'])
            ->withCount(['items', 'items as checked_items_count' => fn ($query) => $query->where('is_checked', true)])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Laboratorium',
            'Tahun Ajaran',
            'Petugas',
            'Item Dicentang',
            'Total Item',
            'Catatan Pemeriksaan Komputer',
            'Catatan Mouse & Keyboard',
            'Catatan UPS',
            'Catatan Monitor',
        ];
    }

    public function map($maintenance): array
    {
        return [
            $maintenance->maintenance_date ? \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d/m/Y') : '-',
            $maintenance->laboratory->name ?? '-',
            $maintenance->academicYear->name ?? '-',
            $maintenance->inspector_name ?: '-',
            $maintenance->checked_items_count,
            $maintenance->items_count,
            $maintenance->notes_computer,
            $maintenance->notes_mouse_keyboard,
            $maintenance->notes_ups,
            $maintenance->notes_monitor,
        ];
    }
}
