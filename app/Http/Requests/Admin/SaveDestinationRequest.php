<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class SaveDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('master-data.manage') || $this->user()?->can('content.manage')) ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $destination = $this->route('destination');

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'name' => ['required', 'string', 'max:150', Rule::unique('destinations', 'name')->where('country_id', $this->input('country_id'))->ignore($destination)],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('destinations', 'slug')->ignore($destination)],
            'region' => ['nullable', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:170'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['boolean'],
            'cover_image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096)],
            'cover_image_alt' => ['nullable', 'required_with:cover_image', 'string', 'max:200'],
            'gallery_image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4096)],
            'gallery_alt' => ['nullable', 'required_with:gallery_image', 'string', 'max:200'],
            'recommended_ids' => ['nullable', 'array', 'max:8'],
            'recommended_ids.*' => ['integer', Rule::exists('destinations', 'id'), Rule::notIn([$destination?->getKey()])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'cover_image_alt.required_with' => 'Describe the cover image for visitors using screen readers and for search engines.',
            'gallery_alt.required_with' => 'Describe the gallery image.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $slug = trim((string) $this->input('slug', ''));

        $this->merge([
            'slug' => $slug === '' ? Str::slug((string) $this->input('name', '')) : Str::lower($slug),
            'is_published' => $this->boolean('is_published'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('is_published') && blank($this->input('summary'))) {
                $validator->errors()->add('summary', 'Add a verified summary before publishing.');
            }
        });
    }
}
