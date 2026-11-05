<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HseInspection extends Model
{
    use HasUuids;

    /** Identitas dokumen Form 04 - Inspeksi HSE (mengikuti form resmi). */
    public const DOC_NUMBER = '004/K3 Lab/UNISA/XI/2024';

    public const DOC_REVISION = '-';

    public const DOC_EFFECTIVE_DATE = '05/11/2024';

    public const KEPALA_NAME = 'Dr. Dhesi Ari Astuti, S.ST., M.Kes.';

    public const KEPALA_ROLE = 'Kepala UPT Laboratorium Unisa Yogyakarta';

    public const KOORDINATOR_NAME = 'Asih Puji Utami, S.KM., M.Kes.';

    public const KOORDINATOR_ROLE = 'Koordinator K3 Laboratorium Unisa Yogyakarta';

    protected $fillable = [
        'kode',
        'lokasi',
        'diperiksa_oleh',
        'tanggal_inspeksi',
        'findings',
        'created_by',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date:Y-m-d',
        'findings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (HseInspection $inspection) {
            if (empty($inspection->kode)) {
                $inspection->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'HSE-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public static function blankFinding(): array
    {
        return [
            'lokasi' => '',
            'temuan' => '',
            'unsafe_condition' => false,
            'unsafe_action' => false,
            'tanggal_kejadian' => null,
            'tindakan_perbaikan' => '',
            'tanggal_perbaikan' => null,
            'pic' => '',
        ];
    }

    /**
     * Rapikan baris temuan dari input form: hanya key yang dikenal yang
     * disimpan dan baris yang benar-benar kosong dibuang.
     */
    public static function normalizeFindings(array $input): array
    {
        $findings = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $finding = [
                'lokasi' => trim((string) ($row['lokasi'] ?? '')),
                'temuan' => trim((string) ($row['temuan'] ?? '')),
                'unsafe_condition' => filter_var($row['unsafe_condition'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'unsafe_action' => filter_var($row['unsafe_action'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'tanggal_kejadian' => ($row['tanggal_kejadian'] ?? null) ?: null,
                'tindakan_perbaikan' => trim((string) ($row['tindakan_perbaikan'] ?? '')),
                'tanggal_perbaikan' => ($row['tanggal_perbaikan'] ?? null) ?: null,
                'pic' => trim((string) ($row['pic'] ?? '')),
            ];

            $isEmpty = $finding['lokasi'] === ''
                && $finding['temuan'] === ''
                && $finding['tindakan_perbaikan'] === ''
                && $finding['pic'] === ''
                && $finding['tanggal_kejadian'] === null
                && $finding['tanggal_perbaikan'] === null
                && ! $finding['unsafe_condition']
                && ! $finding['unsafe_action'];

            if (! $isEmpty) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    /** Baris temuan siap tampil (tanggal sudah diformat dd/mm/yyyy). */
    public function getFindingRowsAttribute(): array
    {
        return collect($this->findings ?? [])
            ->map(fn (array $row) => [
                'lokasi' => (string) ($row['lokasi'] ?? ''),
                'temuan' => (string) ($row['temuan'] ?? ''),
                'unsafe_condition' => (bool) ($row['unsafe_condition'] ?? false),
                'unsafe_action' => (bool) ($row['unsafe_action'] ?? false),
                'tanggal_kejadian' => $row['tanggal_kejadian'] ?? null,
                'tanggal_kejadian_label' => $this->formatDate($row['tanggal_kejadian'] ?? null),
                'tindakan_perbaikan' => (string) ($row['tindakan_perbaikan'] ?? ''),
                'tanggal_perbaikan' => $row['tanggal_perbaikan'] ?? null,
                'tanggal_perbaikan_label' => $this->formatDate($row['tanggal_perbaikan'] ?? null),
                'pic' => (string) ($row['pic'] ?? ''),
            ])
            ->values()
            ->all();
    }

    public function getJumlahTemuanAttribute(): int
    {
        return count($this->finding_rows);
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
            ->orWhere('diperiksa_oleh', 'like', "%{$search}%"));
    }

    private function formatDate(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return '-';
        }
    }
}
