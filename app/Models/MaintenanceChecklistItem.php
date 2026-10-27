<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklistItem extends Model
{
    use HasUuids;

    public const STATUS_SUDAH = 'sudah';

    public const STATUS_BELUM = 'belum';

    public const STATUS_TIDAK_ADA = 'tidak_ada';

    protected $fillable = [
        'maintenance_checklist_id',
        'computer_id',
        'category',
        'item_number',
        'status',
        'checked_by',
        'saved_at',
    ];

    /**
     * Label tampilan per status — dipakai form, laporan, dan cetak.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_SUDAH => 'Sudah',
            self::STATUS_BELUM => 'Belum',
            self::STATUS_TIDAK_ADA => 'Tidak Ada',
        ];
    }

    public static function statusKeys(): array
    {
        return array_keys(static::statuses());
    }

    /**
     * Simbol cetak/tampilan per status: ✓ sudah, ✗ belum, – tidak ada.
     */
    public static function symbols(): array
    {
        return [
            self::STATUS_SUDAH => '✓',
            self::STATUS_BELUM => '✗',
            self::STATUS_TIDAK_ADA => '–',
        ];
    }

    public static function symbol(?string $status): string
    {
        return static::symbols()[$status] ?? '';
    }

    public function checklist()
    {
        return $this->belongsTo(MaintenanceChecklist::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}
