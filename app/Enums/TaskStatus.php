<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending    = 'pending';
    case InProgress = 'in_progress';
    case Done       = 'done';

    public function label(): string
    {
        return match ($this) {
            TaskStatus::Pending    => 'Pending',
            TaskStatus::InProgress => 'In Progress',
            TaskStatus::Done       => 'Done',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
