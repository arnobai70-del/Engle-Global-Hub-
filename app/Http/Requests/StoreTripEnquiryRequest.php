<?php

namespace App\Http\Requests;

use App\Models\Country;
use App\Models\Destination;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "Plan my trip" on Explore Bangladesh, for foreign visitors. The phone number may be
 * from any country (country code, not the Bangladeshi 01XXXXXXXXX format).
 */
class StoreTripEnquiryRequest extends FormRequest
{
    /** What the visitor wants us to arrange, key => English label. */
    public const NEEDS = [
        'hotel' => 'Hotel',
        'airport_pickup' => 'Airport pickup',
        'car_with_driver' => 'Car with driver',
        'tour_guide' => 'Tour guide',
        'domestic_flight' => 'Domestic flight',
        'other' => 'Other',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'residence_country_id' => ['nullable', 'integer', Rule::exists('countries', 'id')],
            'travel_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:travel_date'],
            'travellers' => ['required', 'integer', 'min:1', 'max:20'],
            'destination_ids' => ['nullable', 'array', 'max:14'],
            'destination_ids.*' => ['integer', Rule::in(self::publishedDestinationIds())],
            'needs' => ['nullable', 'array'],
            'needs.*' => [Rule::in(array_keys(self::NEEDS))],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter your phone or WhatsApp number with the country code, for example +44 7700 900123.'),
            'destination_ids.*.in' => __('Please choose places from the list.'),
            'consent.accepted' => __('Please agree to be contacted about your enquiry.'),
        ];
    }

    /**
     * @return list<int>
     */
    public static function publishedDestinationIds(): array
    {
        return Destination::query()->where('is_published', true)->inBangladesh()->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
    }

    public function residenceCountry(): ?Country
    {
        return filled($this->validated('residence_country_id')) ? Country::query()->find($this->validated('residence_country_id')) : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'travellers' => $this->input('travellers', 1),
        ]);
    }
}
