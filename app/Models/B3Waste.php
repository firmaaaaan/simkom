<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B3Waste extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'laboratory_id', 'waste_name', 'waste_type', 'source',
        'quantity', 'unit', 'storage_method', 'disposal_method',
        'disposal_date', 'pic', 'notes',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
}