<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpsertNewsTickerItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'text' => trim(strip_tags((string) $this->input('text'))),
            'text_bn' => filled($this->input('text_bn')) ? trim(strip_tags((string) $this->input('text_bn'))) : null,
            'badge' => filled($this->input('badge')) ? trim(strip_tags((string) $this->input('badge'))) : null,
            'url' => filled($this->input('url')) ? trim((string) $this->input('url')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:140'],
            'text_bn' => ['nullable', 'string', 'max:140'],
            // An internal path ("/study-in-malaysia") or an https link; nothing else (no javascript:, no http:).
            'url' => ['nullable', 'string', 'max:500', 'regex:#^(/(?!/)\S*|https://[^\s/]+\S*)$#'],
            'badge' => ['nullable', 'string', 'max:20'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'url.regex' => 'Use a page on this site starting with "/" (for example /study-in-malaysia) or a full https:// link.',
            'ends_at.after' => 'The end must be after the start.',
            'text.max' => 'Keep the headline to 140 characters so it can be read while it moves.',
        ];
    }
}
