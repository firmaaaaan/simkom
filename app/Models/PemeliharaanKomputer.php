<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeliharaanKomputer extends Model
{
    protected $table = 'pemeliharaan_komputer';

    protected $fillable = [
        'komputer_id',
        'tahun_ajaran_id',
        'tahun_ajaran',
        'tanggal_pemeliharaan',
        'jenis_pemeliharaan',
        'deskripsi',
        'biaya',
        'pic',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemeliharaan' => 'date',
            'biaya' => 'decimal:2',
        ];
    }

    public function komputer(): BelongsTo
    {
        return $this->belongsTo(Komputer::class);
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function getJenisBadgeClass(): string
    {
        return match ($this->jenis_pemeliharaan) {
            'preventif' => 'bg-label-info',
            'korektif' => 'bg-label-warning',
            'upgrade' => 'bg-label-primary',
            'penggantian' => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }
}
