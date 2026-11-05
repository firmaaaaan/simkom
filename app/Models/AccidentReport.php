<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccidentReport extends Model
{
    use HasUuids;

    /** Identitas dokumen Form 03 (tetap, mengikuti form resmi K3 Lab). */
    public const DOC_NUMBER = '003/K3 Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    /** Tempat penandatanganan berita acara. */
    public const SIGN_PLACE = 'Yogyakarta';

    protected $fillable = [
        'kode',
        'tanggal',
        'nama_pelapor',
        'jabatan_pelapor',
        'nama_korban',
        'alamat_korban',
        'ttl_korban',
        'unit_status_korban',
        'uraian_kejadian',
        'dampak',
        'saksi_satu',
        'saksi_dua',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'uraian_kejadian' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (AccidentReport $report) {
            if (empty($report->kode)) {
                $report->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'BAK-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    /**
     * Susun uraian kejadian dari input form: buang baris kosong,
     * rapikan spasi, dan pertahankan urutannya.
     */
    public static function normalizeUraian(array $input): array
    {
        return collect($input)
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }

    /** Baris uraian siap tampil, minimal satu entri kosong. */
    public function getUraianRowsAttribute(): array
    {
        $rows = collect($this->uraian_kejadian ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();

        return $rows === [] ? [''] : $rows;
    }

    /** Nama hari dalam bahasa Indonesia, mis. "Senin". */
    public function getHariAttribute(): string
    {
        return $this->tanggal?->translatedFormat('l') ?? '-';
    }

    /** Tanggal penandatanganan, mis. "5 November 2026". */
    public function getTanggalTandaTanganAttribute(): string
    {
        return $this->tanggal?->translatedFormat('d F Y') ?? '-';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('nama_pelapor', 'like', "%{$search}%")
            ->orWhere('nama_korban', 'like', "%{$search}%")
            ->orWhere('unit_status_korban', 'like', "%{$search}%"));
    }
}
