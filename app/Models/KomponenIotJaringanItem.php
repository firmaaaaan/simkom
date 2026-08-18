<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenIotJaringanItem extends Model
{
    protected $table = 'komponen_iot_jaringan_items';

    protected $fillable = [
        'komponen_iot_jaringan_id',
        'nama_item',
        'spesifikasi',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenIotJaringan::class, 'komponen_iot_jaringan_id');
    }
}
