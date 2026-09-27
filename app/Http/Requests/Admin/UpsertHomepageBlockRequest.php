<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertHomepageBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $existingId = $this->route('homepageBlock')?->getKey();
        $section = (string) $this->input('section');

        return [
            'section' => [
                'required', 'string',
                Rule::in([
                    'assurance', 'promotion', 'service', 'destination',
                    'service_panel', 'benefit', 'testimonial', 'app_feature',
                    'footer_link',
                ]),
            ],
            'key' => [
                'nullable', 'string', 'max:120', 'regex:/^[a-z0-9][a-z0-9_-]*$/',
                Rule::unique('homepage_blocks', 'key')
                    ->where(fn ($query) => $query->where('section', $section))
                    ->ignore($existingId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'image_url' => ['nullable', 'url:http,https', 'max:500'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', Rule::in([
                'plane', 'hotel', 'globe', 'document', 'briefcase',
                'gift', 'train', 'bus', 'car', 'shield', 'star',
                'user', 'check', 'card', 'headset', 'lock', 'list',
            ])],
            'url' => [
                'nullable', 'string', 'max:500',
                'regex:/^(?:https?:\/\/[^\s]+|\/[A-Za-z0-9_\-\/.?=&%#]*)$/',
            ],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'meta' => ['nullable', 'json', 'max:10000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
