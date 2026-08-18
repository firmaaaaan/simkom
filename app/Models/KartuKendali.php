<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KartuKendali extends Model
{
    protected $table = 'kartu_kendali';

    protected $fillable = [
        'inspectable_type',
        'inspectable_id',
        'tanggal_pemeriksaan',
        'pemeriksa',
        'kondisi_keseluruhan',
        'catatan',
        'items',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemeriksaan' => 'date',
            'items' => 'array',
        ];
    }

    public function inspectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}