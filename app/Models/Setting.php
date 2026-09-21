<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public const PUBLIC_SPEC_ID = 1;
    public const REALTIME_SCHEDULE_URL_ID = 2;

    public static function get(int $id, ?string $default = null): ?string
    {
        try {
            $setting = static::find($id);
        } catch (\Illuminate\Database\QueryException $e) {
            return $default;
        }

        return $setting?->value ?? $default;
    }

    public static function set(int $id, ?string $value): void
    {
        static::updateOrCreate(['id' => $id], ['value' => $value]);
    }

    public static function bool(int $id, bool $default = false): bool
    {
        $value = static::get($id);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function publicSpecEnabled(): bool
    {
        return static::bool(self::PUBLIC_SPEC_ID, true);
    }

    public static function realtimeScheduleUrl(): ?string
    {
        $url = static::get(self::REALTIME_SCHEDULE_URL_ID);

        return ($url !== null && $url !== '') ? $url : null;
    }
}
