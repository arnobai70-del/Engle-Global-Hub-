<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $group = (string) $this->route('group');
        $key = (string) $this->route('key');
        $isMetaPixelId = $group === 'analytics' && $key === 'meta_pixel_id';
        $isMetaPixelEnabled = $group === 'analytics' && $key === 'meta_pixel_enabled';

        $valueRules = ['nullable'];

        if ($isMetaPixelId) {
            $valueRules[] = 'string';
            $valueRules[] = 'regex:/^\d{5,30}$/';
        }

        $allowedTypes = match (true) {
            $isMetaPixelId => ['string'],
            $isMetaPixelEnabled => ['boolean'],
            default => [
                'string',
                'integer',
                'float',
                'boolean',
                'json',
            ],
        };

        return [
            'value' => $valueRules,

            'type' => [
                'required',
                'string',
                Rule::in($allowedTypes),
            ],

            'is_public' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.regex' => 'The Meta Pixel ID must contain digits only.',
        ];
    }
}
