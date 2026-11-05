<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OccupationalDiseaseInvestigation extends Model
{
    use HasUuids;

    public const DOC_NUMBER = '008/K3 Lab/UNISA/XI/2024';

    public const DOC_DATE = '15 November 2024';

    protected $fillable = [
        'kode',
        'nama',
        'nip',
        'bagian',
        'jabatan',
        'jenis_kelamin',
        'lama_bekerja',
        'keluhan',
        'riwayat_penyakit',
        'riwayat_penyakit_keluarga',
        'riwayat_pekerjaan',
        'pemeriksaan_fisik',
        'pemeriksaan_radiologi',
        'pemeriksaan_laboratorium',
        'pemeriksaan_penunjang',
        'pemeriksaan_patologi_anatomi',
        'perbandingan_pemeriksaan_awal',
        'perbandingan_pemeriksaan_berkala',
        'tanggal_pemeriksaan',
        'diperiksa_oleh',
        'created_by',
    ];

    protected $casts = [
        'tanggal_pemeriksaan' => 'date:Y-m-d',
    ];

    protected static function booted(): void
    {
        static::creating(function (OccupationalDiseaseInvestigation $inv) {
            if (empty($inv->kode)) {
                $inv->kode = self::generateKode();
            }
        });
    }

    public static function generateKode(): string
    {
        do {
            $code = 'PAK-'.strtoupper(substr(uniqid(), -8));
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
            ->orWhere('nama', 'like', "%{$search}%")
            ->orWhere('bagian', 'like', "%{$search}%")
            ->orWhere('nip', 'like', "%{$search}%"));
    }
}
