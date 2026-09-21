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

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'HW-%')
            ->orderByRaw("CAST(SUBSTR(code, 4) AS INTEGER) DESC")
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^HW-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'HW-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_hardware')->withTimestamps();
    }
}
