<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKendalaKomputer extends Model
{
    protected $table = 'laporan_kendala_komputer';

    protected $fillable = [
        'komputer_id',
        'nama_pelapor',
        'npm_nim',
        'nama_prodi',
        'kategori_kerusakan',
        'deskripsi_kendala',
        'status_kendala',
        'kode_tracker',
        'gambar',
        'tanggal_lapor',
        'tanggal_perbaikan',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lapor' => 'date',
            'tanggal_perbaikan' => 'date',
        ];
    }

    public function komputer(): BelongsTo
    {
        return $this->belongsTo(Komputer::class);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status_kendala) {
            'menunggu' => 'bg-label-warning',
            'diperbaiki' => 'bg-label-info',
            'selesai' => 'bg-label-success',
            default => 'bg-label-secondary',
        };
    }
}
