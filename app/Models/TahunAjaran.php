<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function pemeliharaanKomputer()
    {
        return $this->hasMany(PemeliharaanKomputer::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
