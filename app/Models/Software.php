<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    protected $table = 'software';

    protected $fillable = [
        'kode_software',
        'nama_software',
        'kategori',
        'versi',
        'lisensi',
        'tanggal_instalasi',
        'tanggal_expired',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_instalasi' => 'date',
            'tanggal_expired' => 'date',
        ];
    }

    public function komputers()
    {
        return $this->belongsToMany(Komputer::class, 'komputer_software', 'software_id', 'komputer_id')
            ->withTimestamps();
    }
}
