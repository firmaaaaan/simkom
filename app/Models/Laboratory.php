<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
    use HasUuids;

    protected $attributes = [
        'show_in_schedule' => true,
    ];

    protected $fillable = [
        'name',
        'code',
        'location',
        'capacity',
        'status',
        'show_in_schedule',
        'description',
    ];

    protected $casts = [
        'show_in_schedule' => 'boolean',
    ];

    public function computers(): HasMany
    {
        return $this->hasMany(Computer::class);
    }

    public function layouts(): HasMany
    {
        return $this->hasMany(LabLayout::class);
    }

    public function activeLayout(): HasMany
    {
        return $this->hasMany(LabLayout::class)->published()->latest();
    }
}
