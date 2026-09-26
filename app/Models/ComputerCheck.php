<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ComputerCheck extends Model
{
    use HasUuids;
    protected $fillable = [
        'computer_id',
        'academic_year_id',
        'overall_status',
        'notes',
        'checked_by',
    ];

    /**
     * Saring riwayat berdasarkan bulan dan/atau tahun. Tanpa keduanya = tanpa filter.
     */
    public function scopeForPeriod(Builder $query, ?int $month, ?int $year): Builder
    {
        return $query
            ->when($month >= 1 && $month <= 12, fn (Builder $q) => $q->whereMonth('created_at', $month))
            ->when($year >= 1900 && $year <= 2999, fn (Builder $q) => $q->whereYear('created_at', $year));
    }

    /**
     * Saring riwayat berdasarkan tahun ajaran yang tersimpan di kolom academic_year_id.
     */
    public function scopeForAcademicYear(Builder $query, AcademicYear $academicYear): Builder
    {
        return $query->where('academic_year_id', $academicYear->id);
    }

    /**
     * Daftar tahun yang bisa dipilih pada filter (terbaru di depan).
     * Mengembalikan array kosong bila komputer belum punya riwayat sama sekali.
     */
    public static function availableYears(Computer $computer): array
    {
        $range = static::query()
            ->where('computer_id', $computer->id)
            ->selectRaw('MIN(created_at) as min_created, MAX(created_at) as max_created')
            ->first();

        if (! $range?->min_created) {
            return [];
        }

        $oldest = Carbon::parse($range->min_created)->year;
        $newest = max(Carbon::parse($range->max_created)->year, (int) now()->year);

        return range($newest, $oldest);
    }

    public function computer(): BelongsTo
    {
        return $this->belongsTo(Computer::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function hardwareChecks(): HasMany
    {
        return $this->hasMany(ComputerCheckItem::class, 'computer_check_id');
    }
}
