<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(TaskStatus::values()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, in_progress, done.',
        ];
    }
}
