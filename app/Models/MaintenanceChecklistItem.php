<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklistItem extends Model
{
    protected $fillable = [
        'maintenance_checklist_id',
        'computer_id',
        'category',
        'item_number',
        'is_checked',
        'checked_by',
        'saved_at',
    ];

    public function checklist()
    {
        return $this->belongsTo(MaintenanceChecklist::class);
    }

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }
}
