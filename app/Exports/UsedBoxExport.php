<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Rangkuman box yang pernah dipakai — 1 baris per kode box (unit fisik),
 * tanpa duplikat berapa pun jumlah riwayat pemakaiannya.
 */
class UsedBoxExport extends BaseExport implements WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return $this->query
            ->whereHas('boxUsages')
            ->with(['components:id,name', 'boxComponents.component:id,name'])
            ->withCount('boxUsages as usage_count')
            ->withMax('boxUsages as last_used_at', 'used_at')
            ->withCount('boxComponents as components_count')
            ->withSum('boxComponents as total_quantity', 'quantity')
            ->orderByDesc('last_used_at')
            ->orderBy('code')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Box',
            'Nama Box',
            'Lokasi',
            'Jumlah Pemakaian',
            'Terakhir Dipakai',
            'Jenis Komponen',
            'Total Item Komponen',
            'Status Komponen',
        ];
    }

    public function map($box): array
    {
        return [
            $box->code,
            $box->name,
            $box->location,
            (int) $box->usage_count,
            $box->last_used_at ? Carbon::parse($box->last_used_at)->translatedFormat('d M Y H:i') : '-',
            (int) $box->components_count,
            (int) ($box->total_quantity ?? 0),
            $box->components_count > 0 ? 'Ada' : 'Belum Ada',
        ];
    }
}
