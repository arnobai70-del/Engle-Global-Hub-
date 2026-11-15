<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the editable details of a student application, used when opening
 * an application (directly or from an enquiry) and when updating one.
 */
class StoreStudentApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('student-applications.manage') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'destination_country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'institution_id' => ['nullable', 'integer', Rule::exists('institutions', 'id')],
            'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')->where('is_active', true)],
            'course_intake_id' => ['nullable', 'integer', Rule::exists('course_intakes', 'id')->where('course_id', $this->input('course_id'))],
            'course_name' => ['nullable', 'string', 'max:200'],
            'intake' => ['nullable', 'string', 'max:40'],
            'assigned_consultant_id' => ['nullable', 'integer', Rule::in(self::staffUserIds())],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['assigned_consultant_id.in' => 'The consultant must be a staff account.'];
    }

    /**
     * Validated attributes, with catalogue choices copied into the text
     * fields shown elsewhere (course name, intake label, institution).
     *
     * @return array<string, mixed>
     */
    public function applicationAttributes(): array
    {
        $data = $this->validated();

        if (! empty($data['course_id'])) {
            $course = Course::query()->findOrFail($data['course_id']);
            $data['course_name'] = $course->name;
            $data['institution_id'] = $course->institution_id;
        }

        if (! empty($data['course_intake_id'])) {
            $data['intake'] = CourseIntake::query()->findOrFail($data['course_intake_id'])->label();
        }

        return $data;
    }

    /**
     * @return list<int>
     */
    public static function staffUserIds(): array
    {
        return User::role(explode('|', User::BACK_OFFICE_ROLES))->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
    }
}
