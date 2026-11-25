<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoftwareLicense extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_id',
        'label',
        'license_key',
        'max_devices',
        'purchase_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'max_devices' => 'integer',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'max_devices' => 1,
    ];

    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'software_license_computer')->withTimestamps();
    }

    public function usedSeats(): int
    {
        return $this->relationLoaded('computers') ? $this->computers->count() : $this->computers()->count();
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null
            && $this->expiry_date->lt(now()->startOfDay());
    }

    public function statusLabel(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->expiry_date === null) {
            return 'Tanpa Batas';
        }

        return 'Aktif';
    }

    /**
     * Tampilkan key dalam bentuk tersamarkan, mis. ABCD-••••-••••-WXYZ.
     */
    public function maskedKey(): string
    {
        $key = trim((string) $this->license_key);

        if ($key === '') {
            return '-';
        }

        if (strlen($key) <= 8) {
            return str_repeat('•', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('•', max(4, strlen($key) - 8)).substr($key, -4);
    }
}
