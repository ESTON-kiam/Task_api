<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    // ──────────────────────────────────────────
    //  POST /api/tasks
    // ──────────────────────────────────────────

    /**
     * Create a new task.
     *
     * Rules enforced here (beyond the FormRequest):
     *  - title must be unique per due_date (handled in StoreTaskRequest)
     *  - priority: low | medium | high
     *  - due_date: today or later
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'title'    => $request->title,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status'   => TaskStatus::Pending->value, // always starts as pending
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'data'    => new TaskResource($task),
        ], 201);
    }

    // ──────────────────────────────────────────
    //  GET /api/tasks
    // ──────────────────────────────────────────

    /**
     * List all tasks, sorted high → low priority then due_date ASC.
     * Optionally filter by ?status=pending|in_progress|done
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::query()->sortedByPriorityAndDueDate();

        // Optional status filter
        if ($request->filled('status')) {
            $status = TaskStatus::tryFrom($request->status);

            if ($status === null) {
                return response()->json([
                    'message' => 'Invalid status value. Allowed: pending, in_progress, done.',
                ], 422);
            }

            $query->where('status', $status->value);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found.',
                'data'    => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Tasks retrieved successfully.',
            'total'   => $tasks->count(),
            'data'    => TaskResource::collection($tasks),
        ], 200);
    }

    // ──────────────────────────────────────────
    //  PATCH /api/tasks/{id}/status
    // ──────────────────────────────────────────

    /**
     * Advance a task's status one step forward.
     * Flow: pending → in_progress → done
     * Skipping and reverting are NOT allowed.
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $requested = TaskStatus::tryFrom($request->status);

        // Guard: the requested status must be exactly the next one
        if (! $task->canAdvanceTo($requested)) {
            $current = $task->status->value;
            $allowed = $task->nextStatus()?->value ?? 'none (task is already done)';

            return response()->json([
                'message' => "Invalid status transition. Current status is '{$current}'. Next allowed status is '{$allowed}'.",
            ], 422);
        }

        $task->update(['status' => $requested->value]);

        return response()->json([
            'message' => "Task status updated to '{$requested->value}'.",
            'data'    => new TaskResource($task->fresh()),
        ], 200);
    }

    // ──────────────────────────────────────────
    //  DELETE /api/tasks/{id}
    // ──────────────────────────────────────────

    /**
     * Delete a task. Only tasks with status = done may be deleted.
     */
    public function destroy(Task $task): JsonResponse
    {
        if (! $task->isDeletable()) {
            return response()->json([
                'message' => "Only tasks with status 'done' can be deleted. This task is currently '{$task->status->value}'.",
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ], 200);
    }

    // ──────────────────────────────────────────
    //  GET /api/tasks/report?date=YYYY-MM-DD
    // ──────────────────────────────────────────

    /**
     * Bonus: daily report — task counts per priority × status for a given date.
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = $request->date;

        // Build the summary scaffold so all combinations appear even when count = 0
        $summary = [];
        foreach (TaskPriority::cases() as $priority) {
            foreach (TaskStatus::cases() as $status) {
                $summary[$priority->value][$status->value] = 0;
            }
        }

        // Aggregate from DB
        $rows = Task::query()
            ->whereDate('due_date', $date)
            ->selectRaw('priority, status, COUNT(*) as total')
            ->groupBy('priority', 'status')
            ->get();

        foreach ($rows as $row) {
            $summary[$row->priority->value][$row->status->value] = (int) $row->total;
        }

        return response()->json([
            'date'    => $date,
            'summary' => $summary,
        ], 200);
    }
}
