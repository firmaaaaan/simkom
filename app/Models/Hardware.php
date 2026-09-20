<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hardware extends Model
{
    protected $fillable = [
        'name',
        'code',
        'brand',
        'model',
        'category',
        'quantity',
        'status',
        'description',
    ];

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_hardware')->withTimestamps();
    }
}
