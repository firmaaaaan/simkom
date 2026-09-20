<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location',
        'capacity',
        'status',
        'description',
    ];

    public function computers(): HasMany
    {
        return $this->hasMany(Computer::class);
    }
}
