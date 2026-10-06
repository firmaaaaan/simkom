<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export spesifikasi komputer dalam format lebar: satu baris per komputer,
 * satu kolom per kategori hardware. Sel kosong berarti tidak ada komponen,
 * dan beberapa komponen dalam satu kategori dipisah ";".
 *
 * Format ini sengaja dibuat setara dengan file import
 * (ComputerSpecImport) supaya bisa diedit lalu diimpor balik.
 */
class ComputerSpecExport extends BaseExport implements WithHeadings, WithMapping
{
    /**
     * Urutan kolom kategori yang lazim agar hasil lebih mudah dibaca.
     * Kategori lain di luar daftar ini menyusul secara alfabetis.
     */
    private const CATEGORY_ORDER = [
        'Processor',
        'Motherboard',
        'RAM',
        'Storage',
        'VGA',
        'Monitor',
        'Keyboard',
        'Mouse',
        'Printer',
        'Scanner',
        'Power Supply',
        'Headset',
        'Kabel',
        'Lainnya',
    ];

    private ?array $categories = null;

    public function headings(): array
    {
        return array_merge(
            ['Kode Komputer', 'Laboratorium'],
            $this->categories(),
            ['Keterangan'],
        );
    }

    public function map($computer): array
    {
        $row = [
            $computer->code,
            $computer->laboratory->name ?? '',
        ];

        foreach ($this->categories() as $category) {
            $row[] = $computer->hardware
                ->where('category', $category)
                ->pluck('name')
                ->implode('; ');
        }

        $row[] = $computer->description ?? '';

        return $row;
    }

    /**
     * Kategori hardware yang benar-benar dipakai oleh komputer pada hasil
     * query. Dihitung sendiri (bukan dari collection baris) karena headings()
     * dipanggil library sebelum map().
     */
    private function categories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $this->categories = $this->query->get()
            ->flatMap(fn ($computer) => $computer->hardware)
            ->pluck('category')
            ->filter()
            ->unique()
            ->sortBy(function ($category) {
                $position = array_search($category, self::CATEGORY_ORDER, true);

                return sprintf(
                    '%02d|%s',
                    $position === false ? count(self::CATEGORY_ORDER) : $position,
                    Str::lower($category)
                );
            })
            ->values()
            ->all();

        return $this->categories;
    }
}
