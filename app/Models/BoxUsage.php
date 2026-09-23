<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BoxUsage extends Model
{
    use HasUuids;
    protected $fillable = [
        'box_id',
        'user_name',
        'user_nim',
        'user_kelas',
        'status',
        'used_at',
        'returned_at',
    ];

    protected $casts = [
        'used_at'     => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function box(): BelongsTo
    {
        return $this->belongsTo(Box::class);
    }

    public function returnNote(): HasOne
    {
        return $this->hasOne(BoxReturnNote::class);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->returned_at) {
            return $this->used_at->diffForHumans(null, true);
        }

        return $this->used_at->diff($this->returned_at)->format('%h jam %i menit');
    }
}
