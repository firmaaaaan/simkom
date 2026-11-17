<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiRuang extends Model
{
    use HasUuids;

    protected $table = 'api_ruang';

    protected $fillable = [
        'kdruang',
        'kode',
        'nama',
        'kapasitas',
        'ruang_asli',
        'catatan',
    ];

    protected $casts = [
        'kdruang' => 'integer',
        'kapasitas' => 'integer',
    ];
}
