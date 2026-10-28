<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoChecklistItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'todo_task_id',
        'text',
        'is_done',
        'position',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'position' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(TodoTask::class, 'todo_task_id');
    }
}
