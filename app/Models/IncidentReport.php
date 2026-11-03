<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'laboratory_id', 'incident_date', 'reporter_name', 'incident_type',
        'description', 'impact', 'immediate_action', 'follow_up',
        'status', 'attachment_path',
    ];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
}