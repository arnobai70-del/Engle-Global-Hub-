<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class SaveTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'role_title' => [
                'required', 'string', 'max:150',
                Rule::unique('team_members', 'role_title')->where('name', $this->input('name'))->ignore($this->route('teamMember')),
            ],
            'bio' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['boolean'],
            'photo' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2048)],
            'remove_photo' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['role_title.unique' => 'This person already has a profile with this role.'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'remove_photo' => $this->boolean('remove_photo'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
