<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'    => $this->faker->unique()->sentence(4, true),
            'due_date' => Carbon::today()->addDays(rand(0, 30))->format('Y-m-d'),
            'priority' => $this->faker->randomElement(TaskPriority::values()),
            'status'   => TaskStatus::Pending->value,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => TaskStatus::Pending->value]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => TaskStatus::InProgress->value]);
    }

    public function done(): static
    {
        return $this->state(['status' => TaskStatus::Done->value]);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => TaskPriority::High->value]);
    }
}
