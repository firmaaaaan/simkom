<?php

namespace App\Http\Controllers;

use App\Models\TodoAttachment;
use App\Models\TodoChecklistItem;
use App\Models\TodoColumn;
use App\Models\TodoComment;
use App\Models\TodoLabel;
use App\Models\TodoTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TodoTaskController extends Controller
{
    private const CARD_RELATIONS = ['labels:id,name,color', 'checklistItems:id,todo_task_id,is_done', 'attachments:id,todo_task_id', 'comments:id,todo_task_id'];

    private function ownedTask(TodoTask $task): TodoTask
    {
        abort_unless($task->user_id === auth()->id(), 404);

        return $task;
    }

    private function ownedColumn(string $id): TodoColumn
    {
        return TodoColumn::where('user_id', auth()->id())->findOrFail($id);
    }

    private function cardPayload(TodoTask $task): array
    {
        $task->loadMissing(self::CARD_RELATIONS);

        return $task->toCardArray();
    }

    private function detailPayload(TodoTask $task): array
    {
        $task->load([
            'labels:id,name,color',
            'checklistItems',
            'attachments',
            'comments.user',
        ]);

        return array_merge($task->toCardArray(), [
            'description' => $task->description,
            'checklist' => $task->checklistItems
                ->map(fn (TodoChecklistItem $item) => [
                    'id' => $item->id,
                    'text' => $item->text,
                    'is_done' => $item->is_done,
                ])
                ->values()
                ->all(),
            'attachments' => $task->attachments
                ->map(fn (TodoAttachment $file) => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'human_size' => $file->human_size,
                ])
                ->values()
                ->all(),
            'comments' => $task->comments
                ->map(fn (TodoComment $comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user' => $comment->user->name ?? 'Pengguna',
                    'created_at' => $comment->created_at->diffForHumans(),
                    'can_delete' => $comment->user_id === auth()->id(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function detail(TodoTask $task)
    {
        return response()->json($this->detailPayload($this->ownedTask($task)));
    }

    public function quickAdd(Request $request)
    {
        $validated = $request->validate([
            'column_id' => 'required|uuid',
            'title' => 'required|string|max:255',
        ]);

        $column = $this->ownedColumn($validated['column_id']);

        $task = TodoTask::create([
            'user_id' => auth()->id(),
            'todo_column_id' => $column->id,
            'title' => $validated['title'],
            'position' => (int) TodoTask::where('todo_column_id', $column->id)->max('position') + 1,
        ]);

        return response()->json(['task' => $this->cardPayload($task)], 201);
    }

    public function update(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'column_id' => 'required|uuid',
        ]);

        $column = $this->ownedColumn($validated['column_id']);

        DB::transaction(function () use ($task, $validated, $column) {
            if ($task->todo_column_id !== $column->id) {
                $task->update([
                    'todo_column_id' => $column->id,
                    'position' => (int) TodoTask::where('todo_column_id', $column->id)->where('id', '!=', $task->id)->max('position') + 1,
                ]);
            }

            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'priority' => $validated['priority'] ?? null,
            ]);
        });

        $task->unsetRelation('column');

        return response()->json([
            'task' => $this->cardPayload($task->load(self::CARD_RELATIONS)),
            'message' => 'Kartu berhasil diperbarui.',
        ]);
    }

    public function destroy(TodoTask $task)
    {
        $this->ownedTask($task)->delete();

        return response()->json(['message' => 'Kartu berhasil dihapus.']);
    }

    public function move(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'column_id' => 'required|uuid',
            'position' => 'required|integer|min:0',
        ]);

        $column = $this->ownedColumn($validated['column_id']);

        DB::transaction(function () use ($task, $column, $validated) {
            $oldColumnId = $task->todo_column_id;

            $others = TodoTask::where('todo_column_id', $column->id)
                ->where('id', '!=', $task->id)
                ->orderBy('position')
                ->pluck('id');

            $position = min($validated['position'], $others->count());
            $others->splice($position, 0, [$task->id]);

            $task->update(['todo_column_id' => $column->id]);

            foreach ($others as $index => $taskId) {
                TodoTask::where('id', $taskId)->update(['position' => $index]);
            }

            if ($oldColumnId !== $column->id) {
                TodoTask::where('todo_column_id', $oldColumnId)
                    ->orderBy('position')
                    ->get()
                    ->each(function (TodoTask $remaining, int $index) {
                        if ($remaining->position !== $index) {
                            $remaining->update(['position' => $index]);
                        }
                    });
            }
        });

        return response()->json(['message' => 'Kartu dipindahkan.']);
    }

    public function syncLabels(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'uuid',
        ]);

        $labelIds = TodoLabel::where('user_id', auth()->id())
            ->whereIn('id', $validated['label_ids'] ?? [])
            ->pluck('id')
            ->all();

        $task->labels()->sync($labelIds);

        return response()->json(['task' => $this->cardPayload($task->load(self::CARD_RELATIONS))]);
    }

    public function storeChecklistItem(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'text' => 'required|string|max:500',
        ]);

        $item = TodoChecklistItem::create([
            'todo_task_id' => $task->id,
            'text' => $validated['text'],
            'position' => (int) $task->checklistItems()->max('position') + 1,
        ]);

        return response()->json([
            'item' => [
                'id' => $item->id,
                'text' => $item->text,
                'is_done' => $item->is_done,
            ],
            'checklist_total' => $task->checklistItems()->count(),
            'checklist_done' => $task->checklistItems()->where('is_done', true)->count(),
        ], 201);
    }

    public function toggleChecklistItem(Request $request, TodoChecklistItem $item)
    {
        abort_unless($item->task->user_id === auth()->id(), 404);

        $validated = $request->validate([
            'is_done' => 'required|boolean',
        ]);

        $item->update(['is_done' => $validated['is_done']]);

        $task = $item->task;

        return response()->json([
            'item' => [
                'id' => $item->id,
                'text' => $item->text,
                'is_done' => $item->is_done,
            ],
            'checklist_total' => $task->checklistItems()->count(),
            'checklist_done' => $task->checklistItems()->where('is_done', true)->count(),
        ]);
    }

    public function destroyChecklistItem(TodoChecklistItem $item)
    {
        abort_unless($item->task->user_id === auth()->id(), 404);

        $task = $item->task;
        $item->delete();

        return response()->json([
            'checklist_total' => $task->checklistItems()->count(),
            'checklist_done' => $task->checklistItems()->where('is_done', true)->count(),
        ]);
    }

    public function storeAttachment(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,txt,csv',
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('todo-attachments', 'local');

        $attachment = TodoAttachment::create([
            'todo_task_id' => $task->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => basename($storedPath),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
        ]);

        return response()->json([
            'attachment' => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'human_size' => $attachment->human_size,
            ],
            'attachment_count' => $task->attachments()->count(),
        ], 201);
    }

    public function destroyAttachment(TodoAttachment $attachment)
    {
        abort_unless($attachment->task->user_id === auth()->id(), 404);

        $task = $attachment->task;

        if (Storage::disk('local')->exists('todo-attachments/'.$attachment->stored_name)) {
            Storage::disk('local')->delete('todo-attachments/'.$attachment->stored_name);
        }

        $attachment->delete();

        return response()->json([
            'attachment_count' => $task->attachments()->count(),
        ]);
    }

    public function downloadAttachment(TodoAttachment $attachment)
    {
        abort_unless($attachment->task->user_id === auth()->id(), 404);

        $path = Storage::disk('local')->path('todo-attachments/'.$attachment->stored_name);

        abort_unless(is_file($path), 404);

        return response()->download($path, $attachment->original_name);
    }

    public function storeComment(Request $request, TodoTask $task)
    {
        $task = $this->ownedTask($task);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = TodoComment::create([
            'todo_task_id' => $task->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => auth()->user()->name,
                'created_at' => $comment->created_at->diffForHumans(),
                'can_delete' => true,
            ],
            'comment_count' => $task->comments()->count(),
        ], 201);
    }

    public function destroyComment(TodoComment $comment)
    {
        abort_unless($comment->user_id === auth()->id() && $comment->task->user_id === auth()->id(), 404);

        $task = $comment->task;
        $comment->delete();

        return response()->json([
            'comment_count' => $task->comments()->count(),
        ]);
    }
}
