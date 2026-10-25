<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks.
     */
    public function index(): JsonResponse
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $tasks,
        ], 200);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:pending,completed'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task,
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        return response()->json([
            'data' => $task,
        ], 200);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:pending,completed'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->fresh(),
        ], 200);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found',
            ], 404);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ], 200);
    }
}
