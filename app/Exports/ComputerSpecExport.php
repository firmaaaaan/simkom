<?php

namespace App\Exports;

use App\Models\Hardware;
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
     * Seluruh kategori dari form hardware (Hardware::CATEGORIES) selalu tampil
     * sebagai kolom agar pengguna bisa mengisi kategori yang belum ada data.
     * Kategori di luar daftar yang terpakai di data (mis. hasil import lama)
     * tetap ditambahkan setelahnya secara alfabetis agar round-trip tidak
     * kehilangan data. Dihitung sendiri karena headings() dipanggil library
     * sebelum map().
     */
    private function categories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $used = $this->query->get()
            ->flatMap(fn ($computer) => $computer->hardware)
            ->pluck('category')
            ->filter()
            ->unique();

        $extras = $used
            ->reject(fn ($category) => in_array($category, Hardware::CATEGORIES, true))
            ->sortBy(fn ($category) => Str::lower($category))
            ->values();

        $this->categories = array_merge(Hardware::CATEGORIES, $extras->all());

        return $this->categories;
    }
}
