<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AcademicYear extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'status',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    /**
     * Awal periode tahun ajaran (inklusif, jam 00:00:00): 1 Agustus start_year.
     */
    public function periodStart(): Carbon
    {
        return Carbon::create($this->start_year, 8, 1)->startOfDay();
    }

    /**
     * Akhir periode tahun ajaran (inklusif, sampai 23:59:59): 30 Juni end_year.
     */
    public function periodEnd(): Carbon
    {
        return Carbon::create($this->end_year, 6, 30)->endOfDay();
    }

    public function periodLabel(): string
    {
        return $this->periodStart()->format('d M Y') . ' - ' . $this->periodEnd()->format('d M Y');
    }

    public function coversDate(\DateTimeInterface $date): bool
    {
        $date = Carbon::parse($date);

        return $date->betweenIncluded($this->periodStart(), $this->periodEnd());
    }

    /**
     * Tahun ajaran yang dipakai untuk menandai data baru.
     * Diutamakan tahun berstatus Aktif yang mencakup hari ini; bila tidak ada,
     * dipakai tahun aktif dengan start_year terbaru. Null bila tidak ada yang aktif.
     */
    public static function current(): ?self
    {
        $active = static::active()->orderByDesc('start_year')->get();

        if ($active->isEmpty()) {
            return null;
        }

        return $active->first(fn (self $year) => $year->coversDate(now())) ?? $active->first();
    }
}
