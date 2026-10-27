<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DeviceCheckItem extends Model
{
    use HasUuids;

    public const STATUS_BERFUNGSI = 'berfungsi';

    public const STATUS_RUSAK = 'rusak';

    public const STATUS_TIDAK_ADA = 'tidak_ada';

    protected $fillable = [
        'device_check_id',
        'computer_id',
        'item_key',
        'status',
    ];

    /**
     * Label tampilan per status — dipakai form, laporan, dan cetak.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_BERFUNGSI => 'Berfungsi',
            self::STATUS_RUSAK => 'Rusak',
            self::STATUS_TIDAK_ADA => 'Tidak Ada',
        ];
    }

    public static function statusKeys(): array
    {
        return array_keys(static::statuses());
    }

    /**
     * Simbol cetak/tampilan per status: ✓ berfungsi, ✗ rusak, – tidak ada.
     */
    public static function symbols(): array
    {
        return [
            self::STATUS_BERFUNGSI => '✓',
            self::STATUS_RUSAK => '✗',
            self::STATUS_TIDAK_ADA => '–',
        ];
    }

    public static function symbol(?string $status): string
    {
        return static::symbols()[$status] ?? '';
    }

    public function deviceCheck()
    {
        return $this->belongsTo(DeviceCheck::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}
