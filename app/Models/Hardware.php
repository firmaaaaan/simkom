<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hardware extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'code',
        'brand',
        'model',
        'category',
        'description',
    ];

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_hardware')->withTimestamps();
    }
}
