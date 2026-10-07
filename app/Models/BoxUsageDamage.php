<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Laporan kerusakan komponen di dalam sebuah pemakaian box.
 * Aturan bisnis: satu NIM hanya boleh melaporkan komponen yang sama
 * maksimal satu kali (dicek di controller, lintas sesi pemakaian).
 */
class BoxUsageDamage extends Model
{
    use HasUuids;

    protected $fillable = [
        'box_usage_id',
        'component_id',
        'reported_by',
        'note',
    ];

    public function usage(): BelongsTo
    {
        return $this->belongsTo(BoxUsage::class, 'box_usage_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
