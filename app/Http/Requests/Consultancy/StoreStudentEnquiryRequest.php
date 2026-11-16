<?php

namespace App\Http\Requests\Consultancy;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\StudentEnquiry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentEnquiryRequest extends FormRequest
{
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
            'destination' => ['required', 'string', Rule::in(self::destinations()->pluck('iso2')->all())],
            'study_level' => ['nullable', 'string', Rule::in(array_keys(StudentEnquiry::STUDY_LEVELS))],
            'highest_qualification' => ['nullable', 'string', 'max:120'],
            'preferred_intake' => ['nullable', 'string', 'max:40'],
            'wants_consultation' => ['nullable', 'boolean'],
            'lead_source' => ['nullable', 'string', Rule::in(array_keys(StudentEnquiry::LEAD_SOURCES))],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            // Honeypot: real visitors never see or fill this field.
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid phone number, for example +8801712345678.'),
            'consent.accepted' => __('Please agree to be contacted about your study enquiry.'),
        ];
    }

    /**
     * Active student-visa destinations, managed in Admin › Services & Destinations.
     *
     * @return Collection<int, Country>
     */
    public static function destinations(): Collection
    {
        return Country::query()
            ->select('countries.id', 'countries.name', 'countries.iso2')
            ->join('service_destinations', 'service_destinations.country_id', '=', 'countries.id')
            ->where('service_destinations.service', BusinessService::StudentVisa->value)
            ->where('service_destinations.is_active', true)
            ->where('countries.is_active', true)
            ->orderBy('service_destinations.sort_order')
            ->get();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'destination' => strtoupper(trim((string) $this->input('destination', 'SG'))),
        ]);
    }
}
