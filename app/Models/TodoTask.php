<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TodoTask extends Model
{
    use HasUuids;

    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'user_id',
        'todo_column_id',
        'title',
        'description',
        'due_date',
        'priority',
        'position',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'position' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(TodoColumn::class, 'todo_column_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(TodoLabel::class, 'todo_task_labels', 'todo_task_id', 'todo_label_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TodoChecklistItem::class)->orderBy('position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TodoAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TodoComment::class)->latest();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! $this->checklistItems->every(fn ($item) => $item->is_done);
    }

    public function toCardArray(): array
    {
        $items = $this->checklistItems;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'column_id' => $this->todo_column_id,
            'due_date' => $this->due_date?->toDateString(),
            'priority' => $this->priority,
            'labels' => $this->labels
                ->map(fn (TodoLabel $label) => [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ])
                ->values()
                ->all(),
            'checklist_total' => $items->count(),
            'checklist_done' => $items->where('is_done', true)->count(),
            'attachment_count' => $this->attachments->count(),
            'comment_count' => $this->comments->count(),
        ];
    }
}
