<?php

namespace App\Http\Controllers;

use App\Models\TodoColumn;
use App\Models\TodoLabel;
use App\Models\TodoTask;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $columns = TodoColumn::where('user_id', $user->id)
            ->orderBy('position')
            ->get();

        if ($columns->isEmpty()) {
            foreach (['To Do', 'Doing', 'Done'] as $index => $name) {
                TodoColumn::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'position' => $index,
                ]);
            }

            $columns = TodoColumn::where('user_id', $user->id)
                ->orderBy('position')
                ->get();
        }

        $tasks = TodoTask::where('user_id', $user->id)
            ->with(['labels:id,name,color', 'checklistItems:id,todo_task_id,is_done', 'attachments:id,todo_task_id', 'comments:id,todo_task_id'])
            ->orderBy('position')
            ->get();

        $board = $columns->map(fn (TodoColumn $column) => [
            'id' => $column->id,
            'name' => $column->name,
            'tasks' => $tasks->where('todo_column_id', $column->id)
                ->map(fn (TodoTask $task) => $task->toCardArray())
                ->values()
                ->all(),
        ])->values()->all();

        $labels = TodoLabel::where('user_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->values()
            ->all();

        return view('todos.index', compact('board', 'labels'));
    }
}
