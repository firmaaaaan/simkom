<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTemperatureReading extends Model
{
    use HasUuids;

    protected $table = 'room_temperature_readings';

    protected $fillable = [
        'laboratory_id',
        'temperature',
        'kelembapan',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'temperature' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Status suhu dibandingkan ambang batas ruangan.
     * null bila ambang batas belum diisi atau suhu tidak tersedia.
     */
    public function status(): ?string
    {
        $min = $this->laboratory?->temp_min;
        $max = $this->laboratory?->temp_max;

        if ($min === null || $max === null) {
            return null;
        }

        if ($this->temperature > (float) $max) {
            return 'Panas';
        }

        if ($this->temperature < (float) $min) {
            return 'Dingin';
        }

        return 'Normal';
    }

    public static function statusBadge(?string $status): string
    {
        return match ($status) {
            'Normal' => 'bg-green-100 text-green-700',
            'Panas' => 'bg-red-100 text-red-700',
            'Dingin' => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
