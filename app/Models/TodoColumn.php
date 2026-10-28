<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoColumn extends Model
{
    use HasUuids;

    /**
     * Nama kolom (huruf kecil, tanpa spasi tepi) yang menandai task sudah
     * selesai — task di kolom ini tidak ikut pengingat Telegram H-1/overdue.
     */
    public const DONE_NAMES = ['done', 'selesai'];

    protected $fillable = [
        'user_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TodoTask::class)->orderBy('position');
    }
}
