<?php

namespace App\Http\Requests\Admin;

use App\Models\DestinationListing;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A hotel, restaurant or experience at a destination (Admin › Destinations › edit).
 */
class SaveDestinationListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('master-data.manage') || $this->user()?->can('content.manage')) ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(DestinationListing::TYPES))],
            'name' => ['required', 'string', 'max:150'],
            'area' => ['nullable', 'string', 'max:150'],
            'price_range' => ['nullable', Rule::in(array_keys(DestinationListing::PRICE_RANGES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'website_url' => ['nullable', 'url:https', 'max:2048'],
            'map_url' => ['nullable', 'url:https,http', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['website_url.url' => 'Enter the full website address, starting with https://.'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
