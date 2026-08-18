<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarisIoTJaringanItem extends Model
{
    protected $table = 'inventaris_iot_jaringan_items';

    protected $fillable = [
        'inventaris_iot_jaringan_id',
        'komponen_iot_jaringan_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
        ];
    }

    public function inventaris(): BelongsTo
    {
        return $this->belongsTo(InventarisIoTJaringan::class, 'inventaris_iot_jaringan_id');
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(KomponenIotJaringan::class, 'komponen_iot_jaringan_id');
    }
}
