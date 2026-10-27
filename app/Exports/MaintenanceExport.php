<?php

namespace App\Exports;

use App\Models\MaintenanceChecklistItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

/**
 * Tanpa WithStrictNullComparison, sel bernilai 0 (mis. "Item Belum" = 0)
 * dibuang oleh writer Excel karena perbandingan null yang longgar.
 */
class MaintenanceExport extends BaseExport implements WithHeadings, WithMapping, WithStrictNullComparison
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'academicYear'])
            ->withCount([
                'items',
                'items as sudah_count' => fn ($query) => $query->where('status', MaintenanceChecklistItem::STATUS_SUDAH),
                'items as belum_count' => fn ($query) => $query->where('status', MaintenanceChecklistItem::STATUS_BELUM),
                'items as tidak_ada_count' => fn ($query) => $query->where('status', MaintenanceChecklistItem::STATUS_TIDAK_ADA),
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
            'Item Sudah',
            'Item Belum',
            'Item Tidak Ada',
            'Total Item',
            'Persentase Sudah',
            'Catatan Pemeriksaan Komputer',
            'Catatan Mouse & Keyboard',
            'Catatan UPS',
            'Catatan Monitor',
        ];
    }

    public function map($maintenance): array
    {
        $total = (int) $maintenance->items_count;
        $sudah = (int) $maintenance->sudah_count;
        $belum = (int) $maintenance->belum_count;
        $tidakAda = (int) $maintenance->tidak_ada_count;

        return [
            $maintenance->maintenance_date ? \Carbon\Carbon::parse($maintenance->maintenance_date)->format('d/m/Y') : '-',
            $maintenance->laboratory->name ?? '-',
            $maintenance->academicYear->name ?? '-',
            $maintenance->inspector_name ?: '-',
            $sudah,
            $belum,
            $tidakAda,
            $total,
            $total > 0 ? round($sudah / $total * 100, 1).'%' : '-',
            $maintenance->notes_computer,
            $maintenance->notes_mouse_keyboard,
            $maintenance->notes_ups,
            $maintenance->notes_monitor,
        ];
    }
}
