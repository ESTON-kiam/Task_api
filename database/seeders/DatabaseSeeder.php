<?php

namespace Database\Seeders;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database with realistic sample tasks for demo/testing.
     */
    public function run(): void
    {
        $today = Carbon::today();

        $tasks = [
            [
                'title'    => 'Set up CI/CD pipeline',
                'due_date' => $today->copy()->addDays(1)->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Fix login page bug',
                'due_date' => $today->copy()->addDays(1)->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Write unit tests for auth module',
                'due_date' => $today->copy()->addDays(3)->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Update API documentation',
                'due_date' => $today->copy()->addDays(5)->format('Y-m-d'),
                'priority' => 'medium',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Code review for PR #42',
                'due_date' => $today->copy()->addDays(2)->format('Y-m-d'),
                'priority' => 'medium',
                'status'   => 'in_progress',
            ],
            [
                'title'    => 'Migrate database to new schema',
                'due_date' => $today->copy()->addDays(7)->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Update npm packages',
                'due_date' => $today->copy()->addDays(10)->format('Y-m-d'),
                'priority' => 'low',
                'status'   => 'done',
            ],
            [
                'title'    => 'Clean up unused CSS classes',
                'due_date' => $today->copy()->addDays(14)->format('Y-m-d'),
                'priority' => 'low',
                'status'   => 'pending',
            ],
            [
                'title'    => 'Implement email notifications',
                'due_date' => $today->copy()->addDays(6)->format('Y-m-d'),
                'priority' => 'medium',
                'status'   => 'done',
            ],
            [
                'title'    => 'Deploy hotfix to production',
                'due_date' => $today->format('Y-m-d'),
                'priority' => 'high',
                'status'   => 'done',
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }

        $this->command->info('✅  Seeded ' . count($tasks) . ' sample tasks.');
    }
}
