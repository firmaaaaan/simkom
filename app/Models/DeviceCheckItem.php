<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCheckItem extends Model
{
    protected $fillable = [
        'device_check_id',
        'computer_id',
        'item_key',
        'is_checked',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function deviceCheck()
    {
        return $this->belongsTo(DeviceCheck::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}
