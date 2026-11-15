<?php

namespace App\Http\Requests;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\ServiceDestination;
use App\Models\ServiceEnquiry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $service = ServiceEnquiry::serviceFromSlug((string) $this->route('service'));

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'destination_country_id' => [
                $service->hasDestinations() ? 'required' : 'nullable',
                'integer',
                Rule::in(self::destinationsFor($service)->pluck('id')->all()),
            ],
            'category' => [isset(ServiceEnquiry::CATEGORIES[$service->value]) ? 'required' : 'prohibited', 'nullable', 'string', Rule::in(array_keys(ServiceEnquiry::CATEGORIES[$service->value] ?? []))],
            'route_or_destination' => ['nullable', 'string', 'max:200'],
            'travel_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:travel_date'],
            'travellers' => ['required', 'integer', 'min:1', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number, for example +8801712345678.',
            'destination_country_id.in' => 'Please choose one of the listed destinations.',
            'consent.accepted' => 'Please agree to be contacted about your enquiry.',
        ];
    }

    /**
     * Countries offered for a service: its active destinations when staff
     * manage a list for it, otherwise every active country.
     *
     * @return Collection<int, Country>
     */
    public static function destinationsFor(BusinessService $service): Collection
    {
        $managed = Country::query()
            ->select('countries.id', 'countries.name')
            ->join('service_destinations', 'service_destinations.country_id', '=', 'countries.id')
            ->where('service_destinations.service', $service->value)
            ->where('service_destinations.is_active', true)
            ->where('countries.is_active', true)
            ->orderBy('service_destinations.sort_order')
            ->get();

        if ($managed->isNotEmpty() || ServiceDestination::query()->where('service', $service->value)->exists()) {
            return $managed;
        }

        return Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email', ''))),
            'travellers' => $this->input('travellers', 1),
        ]);
    }
}
