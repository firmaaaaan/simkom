<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;
    /**
     * Key untuk saklar tombol "Lihat Spesifikasi" di halaman publik.
     */
    public const PUBLIC_SPEC = 'public_spec_enabled';

    /**
     * Key untuk URL jadwal real-time yang ditampilkan di halaman jadwal publik.
     */
    public const REALTIME_SCHEDULE_URL = 'realtime_schedule_url';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            $value = static::query()->where('key', $key)->value('value');
        } catch (\Illuminate\Database\QueryException $e) {
            return $default;
        }

        return $value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function publicSpecEnabled(): bool
    {
        return static::bool(self::PUBLIC_SPEC, true);
    }

    public static function realtimeScheduleUrl(): ?string
    {
        $url = static::get(self::REALTIME_SCHEDULE_URL);

        return ($url !== null && $url !== '') ? $url : null;
    }
}
