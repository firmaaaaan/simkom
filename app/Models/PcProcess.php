<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PcProcess extends Model
{
    use HasUuids;

    protected $fillable = [
        'pc_monitor_id',
        'name',
        'window_title',
    ];

    public function pcMonitor(): BelongsTo
    {
        return $this->belongsTo(PcMonitor::class);
    }
}
