<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Laboratory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'location',
        'capacity',
        'temp_min',
        'temp_max',
        'status',
        'show_in_computer_list',
        'description',
    ];

    protected $casts = [
        'show_in_computer_list' => 'boolean',
        'temp_min' => 'float',
        'temp_max' => 'float',
    ];

    public function computers(): HasMany
    {
        return $this->hasMany(Computer::class);
    }

    public function alat(): HasMany
    {
        return $this->hasMany(Alat::class);
    }

    public function temperatureReadings(): HasMany
    {
        return $this->hasMany(RoomTemperatureReading::class)->orderByDesc('recorded_at');
    }

    public function latestTemperatureReading(): ?RoomTemperatureReading
    {
        return $this->temperatureReadings->first();
    }

    /**
     * Relasi pembacaan terbaru (eager-loadable) untuk halaman daftar ruangan.
     */
    public function latestTemperatureReadingRelation(): HasOne
    {
        return $this->hasOne(RoomTemperatureReading::class)->latestOfMany('recorded_at');
    }

    /**
     * Status suhu berdasarkan ambang batas ruangan.
     * null bila ambang batas belum diisi atau pembacaan tidak ada.
     */
    public function temperatureStatus(?RoomTemperatureReading $reading = null): ?string
    {
        $reading ??= $this->latestTemperatureReading();

        if ($reading === null || $this->temp_min === null || $this->temp_max === null) {
            return null;
        }

        if ($reading->temperature > $this->temp_max) {
            return 'Panas';
        }

        if ($reading->temperature < $this->temp_min) {
            return 'Dingin';
        }

        return 'Normal';
    }
}
