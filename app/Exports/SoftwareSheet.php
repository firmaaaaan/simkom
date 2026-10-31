<?php

namespace App\Exports;

use App\Models\Software;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoftwareSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private bool $withData = true)
    {
    }

    public function collection(): Collection
    {
        if (! $this->withData) {
            return collect();
        }

        return Software::orderBy('code')->get();
    }

    public function headings(): array
    {
        return [
            'Kode', 'Nama', 'Versi', 'Kategori', 'Jenis Lisensi',
            'Penanggung Jawab', 'Email PJ', 'Status', 'Keterangan',
            'Penyedia', 'Link Download',
        ];
    }

    public function map($software): array
    {
        return [
            $software->code,
            $software->name,
            $software->version,
            $software->category,
            $software->license_type,
            $software->pic_name,
            $software->pic_email,
            $software->status,
            $software->description,
            $software->provider,
            $software->download_link,
        ];
    }

    public function title(): string
    {
        return 'Software';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
