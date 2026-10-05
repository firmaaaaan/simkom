<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Box extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
        'notes',
    ];

    public function boxComponents(): HasMany
    {
        return $this->hasMany(BoxComponent::class);
    }

    public function components()
    {
        return $this->belongsToMany(Component::class, 'box_components', 'box_id', 'component_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function boxUsages(): HasMany
    {
        return $this->hasMany(BoxUsage::class);
    }

    public function getActiveUsage()
    {
        // Baris Stopped (durasi dihentikan, fisik box belum dicek/dikembalikan)
        // tetap mengunci box agar tidak bisa dipinjamkan lagi lewat scan QR.
        return $this->boxUsages()->whereIn('status', ['Using', 'Stopped'])->first();
    }

    public function getTotalComponentsAttribute(): int
    {
        return $this->boxComponents()->sum('quantity');
    }

    public function getUniqueComponentsCountAttribute(): int
    {
        return $this->boxComponents()->count();
    }
}
