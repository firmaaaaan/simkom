<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'code',
        'category',
        'brand',
        'model',
        'image',
        'quantity',
        'status',
        'description',
    ];

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'KMP-%')
            ->orderByRaw("CAST(SUBSTR(code, 5) AS UNSIGNED) DESC")
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^KMP-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'KMP-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function boxComponents(): HasMany
    {
        return $this->hasMany(BoxComponent::class);
    }

    public function boxes()
    {
        return $this->belongsToMany(Box::class, 'box_components', 'component_id', 'box_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function getBoxesLabelAttribute(): string
    {
        return $this->boxes->pluck('code')->implode(', ') ?: '-';
    }
}
