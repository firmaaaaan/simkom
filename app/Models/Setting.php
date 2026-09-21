<?php

namespace App\Models;

use Illuminate\Support\Facades\File;

class Setting
{
    public const PUBLIC_SPEC = 'public_spec_enabled';
    public const REALTIME_SCHEDULE_URL = 'realtime_schedule_url';

    protected static ?array $cache = null;

    protected static function filePath(): string
    {
        return storage_path('app/settings.json');
    }

    protected static function all(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $path = static::filePath();

        if (!File::exists($path)) {
            return static::$cache = [];
        }

        $data = json_decode(File::get($path), true);

        return static::$cache = is_array($data) ? $data : [];
    }

    protected static function save(array $data): void
    {
        static::$cache = $data;
        File::put(static::filePath(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::all();

        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        $all = static::all();

        if ($value === null) {
            unset($all[$key]);
        } else {
            $all[$key] = $value;
        }

        static::save($all);
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
