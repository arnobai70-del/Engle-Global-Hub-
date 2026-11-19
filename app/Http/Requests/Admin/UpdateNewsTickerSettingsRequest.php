<?php

namespace App\Http\Requests\Admin;

use App\Services\NewsTicker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNewsTickerSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'news_enabled' => ['nullable', 'boolean'],
            'news_speed' => ['required', Rule::in(array_keys(NewsTicker::SPEEDS))],
            'news_label' => ['nullable', 'string', 'max:40'],
            'news_label_bn' => ['nullable', 'string', 'max:40'],
        ];
    }
}
