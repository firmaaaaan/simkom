<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ComputerCheckItem extends Model
{
    protected $fillable = [
        'computer_check_id',
        'checkable_type',
        'checkable_id',
        'status',
        'notes',
    ];

    public function check(): BelongsTo
    {
        return $this->belongsTo(ComputerCheck::class, 'computer_check_id');
    }

    public function checkable(): MorphTo
    {
        return $this->morphTo();
    }
}
