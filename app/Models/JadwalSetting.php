<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalSetting extends Model
{
    protected $table = 'jadwal_settings';

    protected $fillable = [
        'api_url',
        'api_token',
        'tipe',
        'is_active',
        'refresh_interval',
        'last_sync',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'refresh_interval' => 'integer',
            'last_sync' => 'datetime',
        ];
    }
}
