<?php

namespace App\Exports;

use App\Models\SoftwareLicense;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwareLicenseSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private bool $includeKeys = false,
        private bool $withData = true,
    ) {
    }

    public function collection(): Collection
    {
        if (! $this->withData) {
            return collect();
        }

        return SoftwareLicense::with(['software', 'computer'])
            ->orderBy('software_id')
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode Software', 'Label', 'Product Key', 'PC',
            'Tanggal Beli', 'Tanggal Expired', 'Catatan', 'Status',
        ];
    }

    public function map($license): array
    {
        $key = $this->includeKeys
            ? ($license->license_key ?? '')
            : ($license->license_key ? $license->maskedKey() : '');

        return [
            $license->software?->code,
            $license->label,
            $key,
            $license->computer?->code,
            $license->purchase_date?->format('Y-m-d'),
            $license->expiry_date?->format('Y-m-d'),
            $license->notes,
            $license->statusLabel(),
        ];
    }

    public function title(): string
    {
        return 'Lisensi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
