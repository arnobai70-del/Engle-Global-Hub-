<?php

namespace App\Http\Requests\Admin;

use App\Models\MediaAsset;
use App\Models\StudyDestination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudyDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => strtolower(trim((string) $this->input('slug'))),
            'currency_code' => filled($this->input('currency_code')) ? strtoupper(trim((string) $this->input('currency_code'))) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $destination = $this->route('studyDestination');
        $text = fn (int $max): array => ['nullable', 'string', 'max:'.$max];
        $url = ['nullable', 'url:https', 'max:2048'];

        return [
            'slug' => ['required', 'string', 'max:80', 'regex:/^'.StudyDestination::SLUG_PATTERN.'$/', Rule::unique('study_destinations', 'slug')->ignore($destination)],
            'name_bn' => $text(120),
            'hero_title' => $text(160), 'hero_title_bn' => $text(160),
            'intro' => $text(3000), 'intro_bn' => $text(3000),
            'hero_media_id' => ['nullable', 'integer', Rule::exists(MediaAsset::class, 'id')],
            'hero_alt' => $text(200), 'hero_alt_bn' => $text(200),
            'visa_name' => $text(120), 'visa_name_bn' => $text(120),
            'visa_authority' => $text(160), 'visa_authority_bn' => $text(160),
            'processing_time' => $text(200), 'processing_time_bn' => $text(200),
            'processing_source_url' => $url, 'processing_source_label' => $text(160),
            'currency_code' => ['nullable', 'string', 'regex:/^[A-Z]{3}$/'],
            'bdt_rate' => ['nullable', 'numeric', 'gt:0', 'max:100000', 'required_with:bdt_rate_checked_on'],
            'bdt_rate_checked_on' => ['nullable', 'date', 'before_or_equal:today'],
            'intakes' => $text(200), 'intakes_bn' => $text(200),
            'items' => ['nullable', 'array'],
            'items.*' => ['array', 'max:40'],
            'items.*.*.id' => ['nullable', 'integer'],
            'items.*.*.title' => $text(300), 'items.*.*.title_bn' => $text(300),
            'items.*.*.body' => $text(3000), 'items.*.*.body_bn' => $text(3000),
            'items.*.*.amount' => $text(80),
            'items.*.*.source_url' => $url,
            'items.*.*.source_label' => $text(160),
            'items.*.*.needs_verification' => ['nullable', 'boolean'],
            'items.*.*.remove' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use lower-case words joined by dashes, for example "japan" or "united-kingdom".',
            'items.*.*.source_url.url' => 'Every source must be an https link.',
            'processing_source_url.url' => 'The processing time source must be an https link.',
            'bdt_rate.required_with' => 'Enter the rate together with the date you checked it.',
        ];
    }
}
