<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenIotJaringan extends Model
{
    protected $table = 'komponen_iot_jaringan';

    protected $fillable = [
        'kode_komponen',
        'nama_komponen',
        'kategori',
        'merek',
        'spesifikasi',
        'jumlah',
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
}
