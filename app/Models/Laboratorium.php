<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratorium extends Model
{
    protected $table = 'laboratorium';

    protected $fillable = [
        'kode_laboratorium',
        'nama_laboratorium',
        'gedung',
        'ruangan',
        'kapasitas',
        'fasilitas',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas' => 'integer',
        ];
    }

    public function komputers()
    {
        return $this->hasMany(Komputer::class, 'laboratorium_id');
    }
}
