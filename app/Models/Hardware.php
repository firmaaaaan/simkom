<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    protected $table = 'hardware';

    protected $fillable = [
        'kode_hardware',
        'nama_hardware',
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

    public function komputers()
    {
        return $this->belongsToMany(Komputer::class, 'komputer_hardware', 'hardware_id', 'komputer_id')
            ->withPivot('jumlah')
            ->withTimestamps();
    }
}
