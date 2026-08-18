<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanInventarisIoTJaringan extends Model
{
    protected $table = 'peminjaman_inventaris_iot_jaringan';

    protected $fillable = [
        'inventaris_iot_jaringan_id',
        'nama_peminjam',
        'npm_nim',
        'tanggal_pinjam',
        'tanggal_kembali_direncanakan',
        'tanggal_kembali_aktual',
        'status',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali_direncanakan' => 'date',
        'tanggal_kembali_aktual' => 'date',
    ];

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(InventarisIoTJaringan::class, 'inventaris_iot_jaringan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
