<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LabLayout extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'layout_data',
        'grid_cols',
        'grid_rows',
        'cell_size',
        'background_color',
        'is_published',
        'is_draft',
        'laboratory_id',
        'created_by',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_published' => 'boolean',
        'is_draft' => 'boolean',
        'grid_cols' => 'integer',
        'grid_rows' => 'integer',
        'cell_size' => 'integer',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('is_draft', true);
    }

    public function scopeActiveForLab(Builder $query, string $labId): Builder
    {
        return $query->where('laboratory_id', $labId)->published();
    }

    public function getItemsAttribute(): Collection
    {
        return collect($this->layout_data ?? []);
    }

    public function getCanvasWidthAttribute(): int
    {
        return $this->grid_cols * $this->cell_size;
    }

    public function getCanvasHeightAttribute(): int
    {
        return $this->grid_rows * $this->cell_size;
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($layout) {
            if ($layout->is_published) {
                static::where('laboratory_id', $layout->laboratory_id)
                    ->where('id', '!=', $layout->id)
                    ->where('is_published', true)
                    ->update(['is_published' => false]);
            }
        });
    }
}