<?php

namespace App\Exports;

use App\Models\DeviceCheckItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

/**
 * Tanpa WithStrictNullComparison, sel bernilai 0 (mis. "Sel Tidak Ada" = 0)
 * dibuang oleh writer Excel karena perbandingan null yang longgar.
 */
class DeviceCheckExport extends BaseExport implements WithHeadings, WithMapping, WithStrictNullComparison
{
    public function collection(): Collection
    {
        return $this->query
            ->with(['laboratory', 'academicYear'])
            ->withCount([
                'items',
                'items as berfungsi_count' => fn ($query) => $query->where('status', DeviceCheckItem::STATUS_BERFUNGSI),
                'items as rusak_count' => fn ($query) => $query->where('status', DeviceCheckItem::STATUS_RUSAK),
                'items as tidak_ada_count' => fn ($query) => $query->where('status', DeviceCheckItem::STATUS_TIDAK_ADA),
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
            'Sel Rusak',
            'Sel Tidak Ada',
            'Total Sel',
            'Persentase Berfungsi',
            'Catatan',
        ];
    }

    public function map($check): array
    {
        $total = (int) $check->items_count;
        $berfungsi = (int) $check->berfungsi_count;
        $rusak = (int) $check->rusak_count;
        $tidakAda = (int) $check->tidak_ada_count;

        return [
            $check->check_date?->format('d/m/Y'),
            $check->laboratory->name ?? '-',
            $check->academicYear->name ?? '-',
            $check->officer_name ?: '-',
            $check->computers_count,
            $berfungsi,
            $rusak,
            $tidakAda,
            $total,
            $total > 0 ? round($berfungsi / $total * 100, 1).'%' : '-',
            $check->notes,
        ];
    }
}
