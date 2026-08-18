<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenJaringan extends Model
{
    protected $table = 'komponen_iot_jaringan';

    protected $fillable = [
        'kode_komponen',
        'nama_komponen',
        'kategori',
        'merek',
        'spesifikasi',
        'jenis',
        'jumlah',
        'lokasi',
        'status',
        'catatan',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('jaringan', function ($query) {
            $query->where('kategori', 'Jaringan');
        });
    }
}
