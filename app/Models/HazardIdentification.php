<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardIdentification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'laboratory_id', 'area_activity', 'hazard', 'risk',
        'likelihood_before', 'impact_before', 'risk_level_before', 'control_before',
        'schedule', 'likelihood_after', 'impact_after', 'risk_level_after',
        'control_after', 'recommendation', 'authority',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }
}