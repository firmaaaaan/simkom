<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanKomputer extends Model
{
    protected $table = 'peminjaman_komputer';

    protected $fillable = [
        'komputer_id',
        'kode_tracker',
        'nama_peminjam',
        'npm_nim',
        'nama_prodi',
        'catatan',
        'status',
        'status_peminjaman',
        'tanggal_pinjam',
        'jam_mulai',
        'jam_selesai',
        'tanggal_kembali_aktual',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali_aktual' => 'date',
            'jam_mulai' => 'datetime:H:i',
            'jam_selesai' => 'datetime:H:i',
        ];
    }

    public function komputer(): BelongsTo
    {
        return $this->belongsTo(Komputer::class);
    }
}
