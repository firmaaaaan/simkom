<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoxReturnNote extends Model
{
    use HasUuids;

    protected $fillable = [
        'box_usage_id',
        'note',
    ];

    public function boxUsage(): BelongsTo
    {
        return $this->belongsTo(BoxUsage::class);
    }
}
