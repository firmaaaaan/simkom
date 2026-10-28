<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Hardware extends Model
{
    use HasUuids;

    /**
     * Daftar kategori hardware (nilai yang tersimpan di database).
     * Sumber tunggal: dipakai form create/edit dan export spesifikasi.
     */
    public const CATEGORIES = [
        'Processor',
        'RAM',
        'Storage',
        'Motherboard',
        'Power Supply',
        'VGA',
        'Monitor',
        'Keyboard',
        'Mouse',
        'Printer',
        'Scanner',
        'Headset',
        'Kabel',
        'Lainnya',
    ];

    /**
     * Label tampilan tiap kategori untuk dropdown form.
     */
    public const CATEGORY_LABELS = [
        'Processor' => 'Processor (CPU)',
        'RAM' => 'Memory (RAM)',
        'Storage' => 'Storage (HDD/SSD)',
        'Motherboard' => 'Motherboard',
        'Power Supply' => 'Power Supply (PSU)',
        'VGA' => 'VGA (GPU)',
        'Monitor' => 'Monitor',
        'Keyboard' => 'Keyboard',
        'Mouse' => 'Mouse',
        'Printer' => 'Printer',
        'Scanner' => 'Scanner',
        'Headset' => 'Headset/Microphone',
        'Kabel' => 'Kabel/Adapter',
        'Lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'name',
        'code',
        'brand',
        'model',
        'category',
        'description',
    ];

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'HW-%')
            ->orderByRaw('CAST(SUBSTR(code, 4) AS UNSIGNED) DESC')
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^HW-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'HW-'.str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Kelompokkan koleksi hardware per kategori untuk tampilan:
     * [["label" => "Processor (CPU)", "category" => "Processor", "items" => [...]], ...].
     * Urut sesuai CATEGORIES; kategori di luar daftar (mis. hasil import)
     * dikelompokkan di akhir secara alfabet. Komponen tiap grup urut nama.
     * Kategori kosong diperlakukan sebagai "Lainnya".
     *
     * @param  Collection  $items
     * @return array<int, array{label: string, category: string, items: Collection}>
     */
    public static function groupCategories($items): array
    {
        $groups = [];

        foreach ($items as $item) {
            $category = $item->category ?: 'Lainnya';
            $groups[$category][] = $item;
        }

        uksort($groups, function ($a, $b) {
            $indexA = array_search($a, self::CATEGORIES, true);
            $indexB = array_search($b, self::CATEGORIES, true);
            $orderA = $indexA === false ? PHP_INT_MAX : $indexA;
            $orderB = $indexB === false ? PHP_INT_MAX : $indexB;

            return ($orderA <=> $orderB) ?: strcmp($a, $b);
        });

        return array_map(
            fn ($category, $members) => [
                'category' => $category,
                'label' => self::CATEGORY_LABELS[$category] ?? $category,
                'items' => collect($members)->sortBy('name')->values(),
            ],
            array_keys($groups),
            $groups
        );
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_hardware')->withTimestamps();
    }
}
