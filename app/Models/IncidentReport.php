<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IncidentReport extends Model
{
    use HasUuids;

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
        'pelapor',
        'images',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kejadian' => 'date:Y-m-d',
        'images' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (IncidentReport $report) {
            if (empty($report->kode)) {
                $report->kode = self::generateKode();
            }
        });
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
