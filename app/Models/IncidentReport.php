<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IncidentReport extends Model
{
    use HasUuids;

    /**
     * Risk Rating Scale: Likelihood x Impact = Risk Score (1-25).
     * Urutan nilai 5 (tertinggi) sampai 1 (terendah) mengikuti tabel standar.
     */
    public const LIKELIHOODS = [
        5 => 'Hampir Pasti',
        4 => 'Sangat Mungkin',
        3 => 'Mungkin',
        2 => 'Kemungkinan Kecil',
        1 => 'Sangat Kecil Kemungkinan',
    ];

    public const IMPACTS = [
        1 => 'Sangat Kecil',
        2 => 'Kecil',
        3 => 'Sedang',
        4 => 'Besar',
        5 => 'Kritis',
    ];

    /** Batas skor tiap level risiko (inklusif). */
    public const RISK_LEVELS = [
        'Low' => [1, 4],
        'Moderate' => [5, 9],
        'High' => [10, 15],
        'Very High' => [16, 25],
    ];

    /** Pilihan field "Telah terjadi". */
    public const JENIS_KEJADIAN = [
        'Kecelakaan' => 'Kecelakaan',
        'Insiden' => 'Insiden',
        'Nearmiss' => 'Nearmiss (Nyaris celaka)',
        'Duga Bahaya' => 'Duga Bahaya',
    ];

    protected $fillable = [
        'kode',
        'tanggal_kejadian',
        'waktu_kejadian',
        'telah_terjadi',
        'lokasi',
        'nama_korban',
        'jabatan',
        'asal_institusi',
        'sebab',
        'riwayat',
        'saksi',
        'akibat',
        'likelihood',
        'impact',
        'risk_score',
        'risk_level',
        'pelapor',
        'images',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kejadian' => 'date:Y-m-d',
        'images' => 'array',
        'likelihood' => 'integer',
        'impact' => 'integer',
        'risk_score' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (IncidentReport $report) {
            if (empty($report->kode)) {
                $report->kode = self::generateKode();
            }
        });

        // Risk Score selalu dihitung di server agar tidak bisa dimanipulasi dari form.
        static::saving(function (IncidentReport $report) {
            if ($report->likelihood && $report->impact) {
                $report->risk_score = $report->likelihood * $report->impact;
                $report->risk_level = self::riskLevelFor($report->risk_score);
            } else {
                $report->risk_score = null;
                $report->risk_level = null;
            }
        });
    }

    /** Level risiko dari skor 1-25 sesuai Risk Rating Key. */
    public static function riskLevelFor(?int $score): ?string
    {
        if (! $score) {
            return null;
        }

        foreach (self::RISK_LEVELS as $level => [$min, $max]) {
            if ($score >= $min && $score <= $max) {
                return $level;
            }
        }

        return null;
    }

    /** Kelas Tailwind badge untuk level risiko. */
    public static function riskLevelColor(?string $level): string
    {
        return match ($level) {
            'Very High' => 'bg-red-100 text-red-700',
            'High' => 'bg-orange-100 text-orange-700',
            'Moderate' => 'bg-yellow-100 text-yellow-700',
            'Low' => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public static function generateKode(): string
    {
        do {
            $code = 'INS-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('lokasi', 'like', "%{$search}%")
            ->orWhere('nama_korban', 'like', "%{$search}%")
            ->orWhere('pelapor', 'like', "%{$search}%"));
    }

    /** Label tampilan untuk field "Telah terjadi". */
    public function getTelahTerjadiLabelAttribute(): string
    {
        return self::JENIS_KEJADIAN[$this->telah_terjadi] ?? ($this->telah_terjadi ?: '-');
    }

    /** Nama hari dalam bahasa Indonesia, mis. "Senin". */
    public function getHariAttribute(): string
    {
        return $this->tanggal_kejadian?->translatedFormat('l') ?? '-';
    }

    /** Waktu dalam format HH:MM (kolom time tersimpan sebagai string). */
    public function getWaktuAttribute(): string
    {
        return $this->waktu_kejadian ? substr($this->waktu_kejadian, 0, 5) : '-';
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->all();
    }
}
