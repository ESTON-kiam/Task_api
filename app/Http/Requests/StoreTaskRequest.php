<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // open API — no auth required
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                // title must be unique for the same due_date
                Rule::unique('tasks')->where(function ($query) {
                    return $query->whereDate('due_date', $this->due_date);
                }),
            ],
            'due_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today', // must be today or later
            ],
            'priority' => [
                'required',
                Rule::in(TaskPriority::values()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique'              => 'A task with this title already exists for the given due date.',
            'due_date.after_or_equal'   => 'The due date must be today or a future date.',
            'priority.in'               => 'Priority must be one of: low, medium, high.',
        ];
    }
}
