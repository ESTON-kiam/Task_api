<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private string $today;
    private string $tomorrow;

    protected function setUp(): void
    {
        parent::setUp();
        $this->today    = Carbon::today()->format('Y-m-d');
        $this->tomorrow = Carbon::tomorrow()->format('Y-m-d');
    }

    // ──────────────────────────────────────────
    //  POST /api/tasks
    // ──────────────────────────────────────────

    public function test_can_create_task(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Write unit tests',
            'due_date' => $this->tomorrow,
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Write unit tests')
                 ->assertJsonPath('data.status', 'pending');
    }

    public function test_cannot_create_task_with_past_due_date(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title'    => 'Overdue task',
            'due_date' => Carbon::yesterday()->format('Y-m-d'),
            'priority' => 'low',
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_duplicate_title_on_same_due_date(): void
    {
        Task::factory()->create(['title' => 'Same title', 'due_date' => $this->tomorrow]);

        $response = $this->postJson('/api/tasks', [
            'title'    => 'Same title',
            'due_date' => $this->tomorrow,
            'priority' => 'medium',
        ]);

        $response->assertStatus(422);
    }

    public function test_same_title_allowed_on_different_due_dates(): void
    {
        Task::factory()->create(['title' => 'Same title', 'due_date' => $this->tomorrow]);

        $response = $this->postJson('/api/tasks', [
            'title'    => 'Same title',
            'due_date' => Carbon::today()->addDays(2)->format('Y-m-d'),
            'priority' => 'medium',
        ]);

        $response->assertStatus(201);
    }

    // ──────────────────────────────────────────
    //  GET /api/tasks
    // ──────────────────────────────────────────

    public function test_lists_tasks_sorted_by_priority_then_due_date(): void
    {
        Task::factory()->create(['priority' => 'low',    'due_date' => $this->tomorrow]);
        Task::factory()->create(['priority' => 'high',   'due_date' => $this->tomorrow]);
        Task::factory()->create(['priority' => 'medium', 'due_date' => $this->tomorrow]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200);

        $priorities = collect($response->json('data'))->pluck('priority')->toArray();
        $this->assertEquals(['high', 'medium', 'low'], $priorities);
    }

    public function test_returns_meaningful_message_when_no_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'No tasks found.')
                 ->assertJsonPath('data', []);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory()->pending()->create(['due_date' => $this->tomorrow]);
        Task::factory()->inProgress()->create(['due_date' => $this->tomorrow]);

        $response = $this->getJson('/api/tasks?status=pending');

        $response->assertStatus(200);

        foreach ($response->json('data') as $task) {
            $this->assertEquals('pending', $task['status']);
        }
    }

    // ──────────────────────────────────────────
    //  PATCH /api/tasks/{id}/status
    // ──────────────────────────────────────────

    public function test_can_advance_status_from_pending_to_in_progress(): void
    {
        $task = Task::factory()->pending()->create(['due_date' => $this->tomorrow]);

        $response = $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'in_progress']);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_can_advance_status_from_in_progress_to_done(): void
    {
        $task = Task::factory()->inProgress()->create(['due_date' => $this->tomorrow]);

        $response = $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done']);

        $response->assertStatus(200)
                 ->assertJsonPath('data.status', 'done');
    }

    public function test_cannot_skip_status(): void
    {
        $task = Task::factory()->pending()->create(['due_date' => $this->tomorrow]);

        $response = $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'done']);

        $response->assertStatus(422);
    }

    public function test_cannot_revert_status(): void
    {
        $task = Task::factory()->inProgress()->create(['due_date' => $this->tomorrow]);

        $response = $this->patchJson("/api/tasks/{$task->id}/status", ['status' => 'pending']);

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────
    //  DELETE /api/tasks/{id}
    // ──────────────────────────────────────────

    public function test_can_delete_done_task(): void
    {
        $task = Task::factory()->done()->create(['due_date' => $this->tomorrow]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cannot_delete_pending_task(): void
    {
        $task = Task::factory()->pending()->create(['due_date' => $this->tomorrow]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_delete_in_progress_task(): void
    {
        $task = Task::factory()->inProgress()->create(['due_date' => $this->tomorrow]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────
    //  GET /api/tasks/report
    // ──────────────────────────────────────────

    public function test_report_returns_correct_counts(): void
    {
        Task::factory()->create(['priority' => 'high',   'status' => 'pending',     'due_date' => $this->today]);
        Task::factory()->create(['priority' => 'high',   'status' => 'in_progress', 'due_date' => $this->today]);
        Task::factory()->create(['priority' => 'medium', 'status' => 'done',        'due_date' => $this->today]);

        $response = $this->getJson("/api/tasks/report?date={$this->today}");

        $response->assertStatus(200)
                 ->assertJsonPath('summary.high.pending',     1)
                 ->assertJsonPath('summary.high.in_progress', 1)
                 ->assertJsonPath('summary.medium.done',      1)
                 ->assertJsonPath('summary.low.pending',      0);
    }

    public function test_report_requires_date_parameter(): void
    {
        $response = $this->getJson('/api/tasks/report');
        $response->assertStatus(422);
    }
}
