<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentBorrowing extends Model
{
    use HasUuids;

    protected $fillable = [
        'component_id',
        'quantity',
        'user_nim',
        'user_name',
        'status',
        'borrowed_at',
        'returned_at',
        'return_note',
    ];

    protected $casts = [
        'borrowed_at' => 'date',
        'returned_at' => 'datetime',
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }
}
