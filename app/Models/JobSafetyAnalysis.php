<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobSafetyAnalysis extends Model
{
    use HasUuids;

    /**
     * Item APD sesuai Form 06 - Analisis Keselamatan Pekerjaan (JSA).
     * Urutan disusun agar rapi dalam grid 3 kolom seperti form resmi.
     */
    public const APD_ITEMS = [
        'helm' => ['label_id' => 'Helm', 'label' => 'Safety Helmet'],
        'sepatu' => ['label_id' => 'Sepatu', 'label' => 'Safety Shoes'],
        'sarung_tangan' => ['label_id' => 'Sarung Tangan', 'label' => 'Safety Gloves'],
        'rompi' => ['label_id' => 'Rompi Keselamatan', 'label' => 'Safety Vest'],
        'harness' => ['label_id' => 'Pelindung di Ketinggian', 'label' => 'Full Body Harness'],
        'kacamata' => ['label_id' => 'Kacamata Pengaman', 'label' => 'Safety Glasses'],
        'face_shield' => ['label_id' => 'Pelindung Wajah', 'label' => 'Face Shield'],
        'ear_mufs' => ['label_id' => 'Penutup Telinga', 'label' => 'Ear Mufs'],
        'ear_plug' => ['label_id' => 'Penyumbat Telinga', 'label' => 'Ear Plug'],
    ];

    /** Kolom baris langkah pekerjaan JSA. */
    public const STEP_FIELDS = ['langkah', 'bahaya', 'pengendalian', 'penanggung_jawab'];

    /** Identitas dokumen Form 06 (tetap, mengikuti form resmi K3 Lab). */
    public const DOC_NUMBER = '006/K3 Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    protected $fillable = [
        'kode',
        'nama_pekerja',
        'nama_pekerjaan',
        'tanggal_pekerjaan',
        'tempat',
        'apd',
        'apd_lain',
        'steps',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pekerjaan' => 'date:Y-m-d',
        'apd' => 'array',
        'steps' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (JobSafetyAnalysis $jsa) {
            if (empty($jsa->kode)) {
                $jsa->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'JSA-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    /** Susun pilihan APD dari input form: hanya key item yang dikenal yang disimpan. */
    public static function normalizeApd(array $input): array
    {
        $apd = [];

        foreach (array_keys(self::APD_ITEMS) as $key) {
            if (! empty($input[$key])) {
                $apd[$key] = true;
            }
        }

        return $apd;
    }

    /**
     * Susun baris langkah pekerjaan dari input form: buang baris yang
     * seluruh kolomnya kosong dan rapikan spasi tiap kolom.
     */
    public static function normalizeSteps(array $input): array
    {
        return collect($input)
            ->map(fn ($row) => collect(self::STEP_FIELDS)
                ->mapWithKeys(fn ($field) => [$field => trim((string) ($row[$field] ?? ''))])
                ->all())
            ->filter(fn (array $row) => implode('', $row) !== '')
            ->values()
            ->all();
    }

    /** Baris langkah siap tampil, minimal satu entri kosong. */
    public function getStepRowsAttribute(): array
    {
        $rows = self::normalizeSteps($this->steps ?? []);

        return $rows === [] ? [
            array_fill_keys(self::STEP_FIELDS, ''),
        ] : $rows;
    }

    /** Nama hari dalam bahasa Indonesia, mis. "Senin". */
    public function getHariAttribute(): string
    {
        return $this->tanggal_pekerjaan?->translatedFormat('l') ?? '-';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('nama_pekerja', 'like', "%{$search}%")
            ->orWhere('nama_pekerjaan', 'like', "%{$search}%")
            ->orWhere('tempat', 'like', "%{$search}%"));
    }
}
