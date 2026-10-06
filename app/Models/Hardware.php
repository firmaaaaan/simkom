<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            ->orderByRaw("CAST(SUBSTR(code, 4) AS UNSIGNED) DESC")
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^HW-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'HW-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_hardware')->withTimestamps();
    }
}
