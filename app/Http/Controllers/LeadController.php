<?php

namespace App\Http\Controllers;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\ServiceEnquiry;
use App\Models\StudentEnquiry;
use App\Notifications\NewLeadReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The short website forms: free consultation (homepage, Study in Singapore, About),
 * "Get a quote" on every service page, and the contact page message. Each lead is
 * saved in the existing CRM (student or service enquiries) and the team is emailed.
 * Email is optional because many visitors prefer phone or WhatsApp.
 */
class LeadController extends Controller
{
    /** Contact-page topics => where the lead is stored. */
    public const CONTACT_TOPICS = [
        'study' => 'Study in Singapore',
        'visa' => 'Visa',
        'air-ticket' => 'Air ticket',
        'hotel' => 'Hotel booking',
        'tour' => 'Tour or holiday package',
        'other' => 'Something else',
    ];

    /** Quote form: service page slug => stored service. */
    private const QUOTE_SERVICES = [
        'student-visa' => BusinessService::StudentVisa,
        'tourist-visa' => BusinessService::TouristVisa,
        'business-visa' => BusinessService::BusinessVisa,
        'work-visa' => BusinessService::WorkVisa,
        'air-ticket' => BusinessService::AirTicket,
        'business-class' => BusinessService::AirTicket,
        'hotels' => BusinessService::HotelBooking,
        'holiday-packages' => BusinessService::TourPackage,
        'group-tours' => BusinessService::TourPackage,
        'private-tours' => BusinessService::TourPackage,
        'tours' => BusinessService::TourPackage,
        'trains' => BusinessService::Train,
        'bus' => BusinessService::Bus,
        'cars' => BusinessService::Car,
        'bike' => BusinessService::Bike,
        'foreigner-support' => BusinessService::ForeignerSupport,
        'export-import' => BusinessService::ExportImport,
        'civil-construction' => BusinessService::CivilConstruction,
        'visa' => BusinessService::TouristVisa,
    ];

    public function consultation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            ...$this->contactRules(),
            'study_level' => ['nullable', Rule::in(array_keys(StudentEnquiry::STUDY_LEVELS))],
            'preferred_intake' => ['nullable', 'string', 'max:40'],
            'highest_qualification' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:2000'],
            'form' => ['nullable', Rule::in(['home', 'singapore', 'about', 'why-us'])],
        ], $this->messages());

        $lead = $this->studentLead($data, 'Free consultation form ('.($data['form'] ?? 'website').')');

        return $this->thanks($request, $lead->reference, 'consultation-form');
    }

    public function quote(Request $request, string $service): RedirectResponse
    {
        abort_unless(isset(self::QUOTE_SERVICES[$service]), 404);

        $data = $request->validate([
            ...$this->contactRules(),
            'route_or_destination' => ['nullable', 'string', 'max:200'],
            'travel_month' => ['nullable', 'date_format:Y-m'],
            'message' => ['nullable', 'string', 'max:2000'],
        ], $this->messages());

        $lead = ServiceEnquiry::query()->create([
            'reference' => $this->reference(ServiceEnquiry::class, 'EGH-SQ-'),
            'service' => self::QUOTE_SERVICES[$service],
            'user_id' => $request->user()?->getKey(),
            'route_or_destination' => $data['route_or_destination'] ?? null,
            'travel_date' => filled($data['travel_month'] ?? null) ? $data['travel_month'].'-01' : null,
            'travellers' => 1,
            'full_name' => $data['full_name'],
            'email' => filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null,
            'phone' => $data['phone'],
            'message' => $data['message'] ?? null,
            'consented_at' => now(),
        ]);
        NewLeadReceived::notifyTeam($lead, 'Get a quote form ('.$service.')');

        return $this->thanks($request, $lead->reference, 'quote-form');
    }

    public function contact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            ...$this->contactRules(),
            'topic' => ['required', Rule::in(array_keys(self::CONTACT_TOPICS))],
            'message' => ['required', 'string', 'max:2000'],
        ], $this->messages());

        if ($data['topic'] === 'study') {
            $lead = $this->studentLead($data, 'Contact page');
        } else {
            $lead = ServiceEnquiry::query()->create([
                'reference' => $this->reference(ServiceEnquiry::class, 'EGH-SQ-'),
                'service' => match ($data['topic']) {
                    'visa' => BusinessService::TouristVisa,
                    'air-ticket' => BusinessService::AirTicket,
                    'hotel' => BusinessService::HotelBooking,
                    'tour' => BusinessService::TourPackage,
                    default => BusinessService::General,
                },
                'user_id' => $request->user()?->getKey(),
                'route_or_destination' => self::CONTACT_TOPICS[$data['topic']],
                'travellers' => 1,
                'full_name' => $data['full_name'],
                'email' => filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null,
                'phone' => $data['phone'],
                'message' => $data['message'],
                'consented_at' => now(),
            ]);
            NewLeadReceived::notifyTeam($lead, 'Contact page');
        }

        return $this->thanks($request, $lead->reference, 'contact-form');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function studentLead(array $data, string $source): StudentEnquiry
    {
        $lead = StudentEnquiry::query()->create([
            'reference' => $this->reference(StudentEnquiry::class, 'EGH-SE-'),
            'full_name' => $data['full_name'],
            'email' => filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null,
            'phone' => $data['phone'],
            'destination_country_id' => Country::query()->where('iso2', 'SG')->value('id'),
            'highest_qualification' => $data['highest_qualification'] ?? null,
            'study_level' => $data['study_level'] ?? null,
            'preferred_intake' => $data['preferred_intake'] ?? null,
            'wants_consultation' => true,
            'lead_source' => 'direct',
            'message' => $data['message'] ?? null,
            'consented_at' => now(),
        ]);
        NewLeadReceived::notifyTeam($lead, $source);

        return $lead;
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function contactRules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'email' => ['nullable', 'email', 'max:254'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'phone.regex' => __('Enter a valid phone number, for example +8801712345678.'),
            'consent.accepted' => __('Please agree to be contacted about your enquiry.'),
        ];
    }

    private function thanks(Request $request, string $reference, string $defaultAnchor): RedirectResponse
    {
        $anchor = (string) $request->input('return_anchor', $defaultAnchor);

        return redirect()->to(url()->previous(route('home')).'#'.(preg_match('/^[a-z0-9-]{1,40}$/', $anchor) === 1 ? $anchor : $defaultAnchor))
            ->with('lead_reference', $reference);
    }

    /**
     * @param  class-string<StudentEnquiry|ServiceEnquiry>  $model
     */
    private function reference(string $model, string $prefix): string
    {
        do {
            $reference = $prefix.Str::upper(Str::random(10));
        } while ($model::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
