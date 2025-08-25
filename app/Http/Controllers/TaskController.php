<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'required|in:low,medium,high',
        ]);

        $task = Task::create([
            'name' => $validated['name'],
            'comment' => $validated['comment'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'admin_id' => Auth::id(),
            'priority' => $validated['priority'],
        ]);

        return response()->json([
            'success' => 'Задача добавлена',
            'task' => $task,
        ]);
    }

    public function update(Request $request, Task $task)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $task->update([
            'name' => $validated['name'],
            'comment' => $validated['comment'] ?? null,
            'priority' => $validated['priority'],
        ]);

        return response()->json([
            'success' => 'Задача обновлена',
            'task' => $task,
        ]);
    }

    public function destroy(Task $task)
    {

        $task->delete();

        return response()->json([
            'success' => 'Задача удалена',
        ]);
    }
}
