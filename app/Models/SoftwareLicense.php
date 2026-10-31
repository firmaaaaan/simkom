<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoftwareLicense extends Model
{
    use HasUuids;

    protected $fillable = [
        'software_id',
        'label',
        'license_key',
        'assigned_computer_id',
        'purchase_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function software(): BelongsTo
    {
        return $this->belongsTo(Software::class);
    }

    public function computer(): BelongsTo
    {
        return $this->belongsTo(Computer::class, 'assigned_computer_id');
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
