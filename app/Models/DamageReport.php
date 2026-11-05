<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    use HasUuids;

    public const DOC_NUMBER = '007/K3 Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    public const KEPALA_NAME = 'Dr. Dhesi Ari Astuti, S.ST., M.Kes.';

    public const JENIS = [
        'kerusakan' => 'Kerusakan',
        'kehilangan' => 'Kehilangan',
        'pencurian' => 'Pencurian',
    ];

    protected $fillable = [
        'kode',
        'pelapor_nama',
        'pelapor_ruangan',
        'pelapor_telepon',
        'tanggal_insiden',
        'waktu_kejadiaan',
        'tanggal_dilaporkan',
        'waktu_dilaporkan',
        'lokasi_spesifik',
        'jenis_kejadian',
        'peralatan',
        'deskripsi_kejadian',
        'perkiraan_biaya',
        'penanggung_jawab',
        'dilaporkan_ke_polisi',
        'berkas_polisi',
        'petugas_polisi',
        'unit_kepolisian',
        'telepon_polisi',
        'created_by',
    ];

    protected $casts = [
        'tanggal_insiden' => 'date:Y-m-d',
        'tanggal_dilaporkan' => 'date:Y-m-d',
        'peralatan' => 'array',
        'dilaporkan_ke_polisi' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (DamageReport $report) {
            if (empty($report->kode)) {
                $report->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'KRK-'.strtoupper(substr(uniqid(), -8));
        } while (static::where('kode', $code)->exists());

        return $code;
    }

    public static function blankPeralatan(): array
    {
        return [
            'nama' => '',
            'merk' => '',
            'no_inventaris' => '',
            'jumlah' => '',
            'keterangan' => '',
        ];
    }

    public static function normalizePeralatan(array $input): array
    {
        $items = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $item = [
                'nama' => trim((string) ($row['nama'] ?? '')),
                'merk' => trim((string) ($row['merk'] ?? '')),
                'no_inventaris' => trim((string) ($row['no_inventaris'] ?? '')),
                'jumlah' => trim((string) ($row['jumlah'] ?? '')),
                'keterangan' => trim((string) ($row['keterangan'] ?? '')),
            ];

            if (collect($item)->every(fn ($v) => $v === '')) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    public function getPeralatanRowsAttribute(): array
    {
        return collect($this->peralatan ?? [])->values()->all();
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis_kejadian] ?? ucfirst($this->jenis_kejadian);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn (Builder $q) => $q
            ->where('kode', 'like', "%{$search}%")
            ->orWhere('pelapor_nama', 'like', "%{$search}%")
            ->orWhere('pelapor_ruangan', 'like', "%{$search}%")
            ->orWhere('deskripsi_kejadian', 'like', "%{$search}%"));
    }
}
