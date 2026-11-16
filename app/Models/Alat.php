<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alat extends Model
{
    use HasUuids;

    protected $table = 'alat';

    protected $fillable = [
        'laboratory_id',
        'name',
        'code',
        'merk_type',
        'location',
        'status',
        'description',
    ];

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function checks(): HasMany
    {
        return $this->hasMany(AlatCheck::class)->orderByDesc('check_date');
    }

    public function latestCheck(): ?AlatCheck
    {
        return $this->checks->first();
    }

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'ALT-%')
            ->orderByRaw('CAST(SUBSTR(code, 5) AS UNSIGNED) DESC')
            ->value('code');

        $next = 1;
        if ($last && preg_match('/^ALT-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'ALT-'.str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
