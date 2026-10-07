<?php

namespace App\Exports;

use App\Models\MaintenanceChecklist;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import pemeliharaan. Judul kolom item diambil dari
 * MaintenanceChecklist::getChecklistItems() (teks pertanyaan lengkap) sehingga
 * otomatis mengikuti perubahan checklist. Baris contoh memakai kode "Contoh"
 * agar otomatis dilewati saat diunggah apa adanya.
 */
class MaintenanceTemplate implements FromCollection, WithHeadings, WithStyles
{
    public function headings(): array
    {
        $headings = ['No', 'Kode Komputer'];

        foreach (MaintenanceChecklist::getChecklistItems() as $definition) {
            foreach ($definition['items'] as $question) {
                $headings[] = $question;
            }
        }

        return $headings;
    }

    public function collection(): Collection
    {
        $itemCount = collect(MaintenanceChecklist::getChecklistItems())
            ->sum(fn ($definition) => count($definition['items']));

        return collect([
            array_merge([1, 'Contoh'], array_fill(0, $itemCount, '✓')),
            array_merge([2, 'Contoh'], array_fill(0, $itemCount, '')),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
