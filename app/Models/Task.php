<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'priority'   => TaskPriority::class,
        'status'     => TaskStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ──────────────────────────────────────────
    //  Business logic helpers
    // ──────────────────────────────────────────

    /**
     * Return the next valid status after the current one.
     * Returns null when the task is already "done".
     */
    public function nextStatus(): ?TaskStatus
    {
        return match ($this->status) {
            TaskStatus::Pending    => TaskStatus::InProgress,
            TaskStatus::InProgress => TaskStatus::Done,
            TaskStatus::Done       => null,
        };
    }

    /**
     * Whether the task can be advanced to the given status.
     * Only one step forward is allowed; no skipping, no reverting.
     */
    public function canAdvanceTo(TaskStatus $target): bool
    {
        return $this->nextStatus() === $target;
    }

    /**
     * Whether this task is eligible for deletion (only done tasks).
     */
    public function isDeletable(): bool
    {
        return $this->status === TaskStatus::Done;
    }

    // ──────────────────────────────────────────
    //  Priority sort order (high=1, medium=2, low=3)
    // ──────────────────────────────────────────

    public function scopeSortedByPriorityAndDueDate($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'high'   THEN 1
                WHEN 'medium' THEN 2
                WHEN 'low'    THEN 3
            END ASC
        ")->orderBy('due_date', 'asc');
    }
}
