<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudentApplicationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionStudentApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('student-applications.manage') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(StudentApplicationStatus::class)],
            'student_note' => ['nullable', 'string', 'max:1000'],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function targetStatus(): StudentApplicationStatus
    {
        return StudentApplicationStatus::from($this->validated('status'));
    }
}
