<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlatCheck extends Model
{
    use HasUuids;

    public const STATUSES = ['Baik', 'Rusak'];

    protected $fillable = [
        'alat_id',
        'check_date',
        'status',
        'notes',
        'checked_by',
    ];

    protected $casts = [
        'check_date' => 'date:Y-m-d',
    ];

    public function alat(): BelongsTo
    {
        return $this->belongsTo(Alat::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public static function statusBadge(string $status): string
    {
        return $status === 'Baik'
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-700';
    }
}
