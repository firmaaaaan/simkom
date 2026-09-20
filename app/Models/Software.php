<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Software extends Model
{
    protected $fillable = [
        'name',
        'code',
        'version',
        'license_type',
        'category',
        'license_count',
        'status',
        'description',
    ];

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_software')->withTimestamps();
    }
}
