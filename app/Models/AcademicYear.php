<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }

    /**
     * Awal periode tahun ajaran (inklusif, jam 00:00:00).
     * Bila start_date kosong, dipakai 1 Agustus dari start_year.
     */
    public function periodStart(): Carbon
    {
        return $this->start_date
            ? $this->start_date->copy()->startOfDay()
            : Carbon::create($this->start_year, 8, 1)->startOfDay();
    }

    /**
     * Akhir periode tahun ajaran (inklusif, sampai 23:59:59).
     * Bila end_date kosong, dipakai 30 Juni dari end_year.
     */
    public function periodEnd(): Carbon
    {
        return $this->end_date
            ? $this->end_date->copy()->endOfDay()
            : Carbon::create($this->end_year, 6, 30)->endOfDay();
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
