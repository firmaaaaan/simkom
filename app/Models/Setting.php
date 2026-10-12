<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Setting extends Model
{
    protected $fillable = [
        'id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public $incrementing = false;

    protected $keyType = 'int';

    public const PUBLIC_SPEC_ID = 1;

    public static function get(int $id, ?string $default = null): ?string
    {
        try {
            $setting = static::find($id);
        } catch (QueryException $e) {
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
}
