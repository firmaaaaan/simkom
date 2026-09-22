<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Software extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'code',
        'version',
        'license_type',
        'category',
        'status',
        'description',
    ];

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'SW-%')
            ->orderByRaw("CAST(SUBSTR(code, 4) AS UNSIGNED) DESC")
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^SW-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'SW-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    public function computers(): BelongsToMany
    {
        return $this->belongsToMany(Computer::class, 'computer_software')->withTimestamps();
    }
}
