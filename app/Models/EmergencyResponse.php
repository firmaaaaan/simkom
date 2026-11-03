<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyResponse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'laboratory_id', 'scenario', 'procedure', 'responsible_person',
        'contact', 'equipment', 'drill_date', 'notes',
    ];

    protected $casts = [
        'drill_date' => 'date',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
}