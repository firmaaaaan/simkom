<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarisIoTJaringan extends Model
{
    protected $table = 'inventaris_iot_jaringan';

    protected $fillable = [
        'nama_inventaris',
        'kode_perangkat',
        'kategori',
        'jenis',
        'lokasi',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventarisIoTJaringanItem::class, 'inventaris_iot_jaringan_id');
    }

    public function kartuKendali()
    {
        return $this->morphMany(KartuKendali::class, 'inspectable');
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanInventarisIoTJaringan::class, 'inventaris_iot_jaringan_id');
    }
}
