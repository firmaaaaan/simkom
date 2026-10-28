<?php

namespace App\Http\Controllers;

use App\Models\TodoColumn;
use App\Models\TodoTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TodoColumnController extends Controller
{
    private function ownedColumn(string $id): TodoColumn
    {
        return TodoColumn::where('user_id', auth()->id())->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $column = TodoColumn::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'position' => TodoColumn::where('user_id', auth()->id())->count(),
        ]);

        return response()->json([
            'column' => ['id' => $column->id, 'name' => $column->name, 'tasks' => []],
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $column = $this->ownedColumn($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $column->update($validated);

        return response()->json([
            'column' => ['id' => $column->id, 'name' => $column->name],
        ]);
    }

    public function destroy(string $id)
    {
        $column = $this->ownedColumn($id);

        $result = DB::transaction(function () use ($column) {
            $target = TodoColumn::where('user_id', auth()->id())
                ->where('id', '!=', $column->id)
                ->orderBy('position')
                ->first();

            $movedTasks = collect();

            if ($target) {
                $tasks = TodoTask::where('todo_column_id', $column->id)
                    ->with(['labels:id,name,color', 'checklistItems:id,todo_task_id,is_done', 'attachments:id,todo_task_id', 'comments:id,todo_task_id'])
                    ->orderBy('position')
                    ->get();

                $nextPosition = (int) TodoTask::where('todo_column_id', $target->id)->max('position') + 1;

                foreach ($tasks as $task) {
                    $task->update(['todo_column_id' => $target->id, 'position' => $nextPosition++]);
                    $movedTasks->push($task->toCardArray());
                }
            } else {
                TodoTask::where('todo_column_id', $column->id)->delete();
            }

            TodoColumn::where('user_id', auth()->id())
                ->where('id', '!=', $column->id)
                ->orderBy('position')
                ->get()
                ->each(function (TodoColumn $remaining, int $index) {
                    if ($remaining->position !== $index) {
                        $remaining->update(['position' => $index]);
                    }
                });

            $column->delete();

            return [
                'target_column_id' => $target?->id,
                'moved_tasks' => $movedTasks->values()->all(),
            ];
        });

        return response()->json(array_merge(['message' => 'Kolom berhasil dihapus.'], $result));
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'columns' => 'required|array|min:1',
            'columns.*' => 'uuid',
        ]);

        $columns = TodoColumn::where('user_id', auth()->id())
            ->whereIn('id', $validated['columns'])
            ->get()
            ->keyBy('id');

        if ($columns->count() !== count($validated['columns'])) {
            return response()->json(['message' => 'Kolom tidak ditemukan.'], 404);
        }

        foreach ($validated['columns'] as $index => $columnId) {
            $columns[$columnId]->update(['position' => $index]);
        }

        return response()->json(['message' => 'Urutan kolom diperbarui.']);
    }
}
