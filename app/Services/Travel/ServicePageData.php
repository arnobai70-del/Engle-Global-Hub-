<?php

namespace App\Services\Travel;

use App\Models\ServiceDestination;
use App\Services\SiteFacts;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Builds the content of the shared service page template (DESIGN_BRIEF.md "Service pages")
 * for every service: /services/*, /visa, /hotels, /tours, /work-visa and /flights.
 * Group defaults supply the steps, documents and disclaimer; services add their own
 * headline, introduction and what is included. Fees and reply times come only from
 * Admin › Business facts, so nothing here is a made-up number.
 */
class ServicePageData
{
    /**
     * Services that have their own top-level URL instead of /services/{slug}.
     *
     * @var array<string, array<string, mixed>>
     */
    private const STANDALONE = [
        'visa' => [
            'title' => 'Visa Services', 'group' => 'visa', 'route' => 'visa.index', 'countries' => 'tourist_visa',
            'headline' => 'Visa help for students, tourists and business travellers',
            'intro' => 'Tell us where you are going and why. A consultant checks the requirements for your passport, reviews your documents and prepares your application with you.',
            'included' => ['Requirement check for your passport and destination', 'Document review before you submit', 'Embassy or VFS appointment booking', 'Hotel and ticket bookings for your visa file'],
            'related' => ['tourist-visa', 'business-visa', 'work-visa', 'student-visa'],
        ],
        'work-visa' => [
            'title' => 'Work Visa', 'group' => 'visa', 'route' => 'work-visa.index', 'countries' => 'work_visa',
            'headline' => 'Work visa guidance',
            'intro' => 'Information and document guidance for work permits. We explain the official process for your destination and help you prepare a genuine, complete application.',
            'included' => ['Explanation of the official work permit process', 'Checklist of the documents your employer and you must provide', 'Review of your documents before submission', 'Air ticket and travel arrangements once your permit is approved'],
            'disclaimer' => 'Work permits are issued by the destination country\'s authorities, usually through a real employer. We never sell jobs or visas, and we cannot guarantee approval.',
            'related' => ['tourist-visa', 'air-ticket', 'foreigner-support'],
        ],
        'hotels' => [
            'title' => 'Hotels', 'group' => 'travel', 'route' => 'hotels.index', 'capability' => 'hotels',
            'headline' => 'Hotels and student housing',
            'intro' => 'Tell us the city, dates and budget. Our team suggests hotels, shared rooms or student hostels and confirms the details with you before anything is booked.',
            'included' => ['Hotels for holidays and business trips', 'Shared rooms and student hostels in Singapore', 'Hotel bookings for visa applications', 'Airport arrival guidance'],
            'related' => ['holiday-packages', 'air-ticket', 'tourist-visa'],
        ],
        'tours' => [
            'title' => 'Tours', 'group' => 'travel', 'route' => 'tours.index', 'capability' => 'tours',
            'headline' => 'Tours and holiday packages',
            'intro' => 'Holidays in Bangladesh and abroad, planned around your dates and budget. We combine flights, hotels, transfers and sightseeing in one written quote.',
            'included' => ['Family, honeymoon and group holidays', 'Flights, hotels and transfers together', 'Sightseeing and day tours', 'Visa support where required'],
            'related' => ['holiday-packages', 'group-tours', 'private-tours', 'hotels'],
        ],
        'flights' => [
            'title' => 'Air Tickets', 'group' => 'travel', 'route' => 'flights.index', 'capability' => 'flights', 'quote' => 'air-ticket',
            'headline' => 'Air tickets for students, families and business trips',
            'intro' => 'Send your route and dates. Our team compares airlines and replies with fare options, baggage rules and the total price before you pay.',
            'included' => ['One-way, return and multi-city tickets', 'Student fares and extra baggage where airlines offer them', 'Business class quotes', 'Date changes and booking support'],
            'topic_label' => 'From – to (for example Dhaka – Singapore)',
            'related' => ['business-class', 'hotels', 'tourist-visa'],
        ],
    ];

    /** Per-service text for /services/{slug}. */
    private const SERVICES = [
        'student-visa' => [
            'headline' => 'Student visa and Student\'s Pass support',
            'intro' => 'Singapore is our main study destination. We help you choose a course, apply for admission and prepare your Student\'s Pass application with ICA, step by step.',
        ],
        'tourist-visa' => [
            'headline' => 'Tourist visa help',
            'headline_count' => 'Tourist visa help for :count countries',
            'intro' => 'Tell us where you want to go. A consultant checks the requirements for your passport, reviews your documents and books your appointment.',
            'included' => ['Country-specific requirement check for your passport', 'Document review before you submit', 'Embassy / VFS appointment booking', 'Travel insurance, hotel and ticket bookings for the file'],
        ],
        'business-visa' => [
            'headline' => 'Business visa help',
            'intro' => 'For meetings, conferences and trade visits. We check the invitation and supporting documents your destination needs and prepare the application with you.',
        ],
        'air-ticket' => [
            'headline' => 'Air tickets for students, families and business trips',
            'intro' => 'Send your route and dates. Our team compares airlines and replies with fare options, baggage rules and the total price before you pay.',
            'included' => ['One-way, return and multi-city tickets', 'Student and family travel', 'Business class quotes', 'Date changes and booking support'],
            'topic_label' => 'From – to (for example Dhaka – Singapore)',
        ],
        'business-class' => [
            'headline' => 'Business class fares',
            'intro' => 'Our team compares business class fares across airlines and explains lounge, baggage and seat details before you decide.',
            'topic_label' => 'From – to',
        ],
        'holiday-packages' => ['headline' => 'Holiday packages', 'intro' => 'Holidays planned around your dates, budget and destination, with flights, hotels and tours in one written quote.'],
        'group-tours' => ['headline' => 'Group tours', 'intro' => 'Tours for friends, families, students and companies, with one contact person for the whole group.'],
        'private-tours' => ['headline' => 'Private tours', 'intro' => 'Your own schedule, private transport and hotels chosen for you.'],
        'trains' => ['headline' => 'Train tickets', 'intro' => 'Route and timetable advice and ticket booking support from our team.', 'topic_label' => 'From – to'],
        'bus' => ['headline' => 'Bus tickets', 'intro' => 'Operator and route advice for local and cross-border buses, with booking support.', 'topic_label' => 'From – to'],
        'cars' => ['headline' => 'Cars and airport transfers', 'intro' => 'Airport pick-up and drop-off, day hire and city transfers with a driver.', 'topic_label' => 'Pick-up and drop-off'],
        'bike' => ['headline' => 'Bike rental', 'intro' => 'Bike rental arranged through our team for local trips.', 'topic_label' => 'Where and when'],
        'foreigner-support' => ['headline' => 'Support for foreigners in Bangladesh', 'intro' => 'Help for foreign visitors and students: travel, accommodation, local transport and visa information.', 'topic_label' => 'What do you need help with?'],
        'export-import' => ['headline' => 'Export & import enquiries', 'intro' => 'Send your requirement and our team replies personally.', 'topic_label' => 'Product or requirement'],
        'civil-construction' => ['headline' => 'Civil & construction work', 'intro' => 'Enquiries for civil construction and land-related work. Our team replies personally.', 'topic_label' => 'Project or location'],
    ];

    /** Defaults per service group. */
    private const GROUPS = [
        'study' => [
            'steps' => [['Offer letter', 'The institution issues your offer and registers you with ICA.'], ['eForm 16', 'We help you complete the online application.'], ['In-principle approval', 'ICA reviews it; you pay the issuance fee.'], ['Arrival', 'Medical check and Student\'s Pass card in Singapore.']],
            'documents' => ['Passport (valid 6+ months)', 'SSC & HSC certificates and transcripts', 'Degree certificate (if any)', 'English test result', 'Bank statement / sponsor letter', 'Passport-size photos'],
            'disclaimer' => 'Admission is decided by each institution and the Student\'s Pass by Singapore\'s ICA. We prepare a complete application; we never promise approval.',
            'topic_label' => 'Study level or course',
            'pay' => 'Consultation is free',
        ],
        'visa' => [
            'steps' => [['Send enquiry', 'Country, dates and passport.'], ['Get a quote', 'Fee and checklist in writing.'], ['We prepare', 'Documents checked, appointment booked.'], ['Submit', 'You attend; we follow it until the decision.']],
            'documents' => ['Passport (6+ months valid)', 'Photos to embassy size', 'Bank statement', 'NOC / trade licence', 'Return ticket booking', 'Hotel booking'],
            'disclaimer' => 'Visa decisions are made by the embassy. We prepare and check your application; we cannot guarantee approval.',
            'topic_label' => 'Destination country',
            'pay' => 'Only after quote',
        ],
        'travel' => [
            'steps' => [['Send enquiry', 'Destination, dates and travellers.'], ['Get options', 'Choices and the total price in writing.'], ['Confirm', 'You choose; we book and send the details.'], ['Travel', 'Our team stays reachable during your trip.']],
            'documents' => ['Passport copy (for international trips)', 'Travel dates and number of travellers', 'Visa, if your destination needs one'],
            'disclaimer' => 'Prices and availability are confirmed only in your written quote. Nothing is booked or charged until you confirm.',
            'topic_label' => 'Destination',
            'pay' => 'Only after quote',
        ],
        'transport' => [
            'steps' => [['Send enquiry', 'Route, date and passengers.'], ['Get options', 'Times and fares in writing.'], ['Confirm', 'You choose; we arrange the booking.'], ['Travel', 'Tickets or driver details sent to you.']],
            'documents' => ['Route and travel date', 'Number of passengers', 'ID or passport for cross-border trips'],
            'disclaimer' => 'Fares and times are confirmed only in your written quote. Nothing is booked until you confirm.',
            'topic_label' => 'Route',
            'pay' => 'Only after quote',
        ],
        'business' => [
            'steps' => [['Send enquiry', 'Tell us what you need.'], ['We call you', 'To understand the details.'], ['Written proposal', 'Scope, timeline and price agreed in writing.'], ['Work begins', 'One contact person follows it.']],
            'documents' => ['A short description of your requirement', 'Any drawings, specifications or permits you already have'],
            'disclaimer' => 'Scope and price are agreed in writing before any work starts.',
            'topic_label' => 'Requirement',
            'pay' => 'Only after quote',
        ],
    ];

    public function __construct(private ServiceCatalog $catalog, private SiteFacts $facts) {}

    /**
     * Every slug that has a service page, including the standalone ones.
     *
     * @return list<string>
     */
    public function slugs(): array
    {
        return [...array_keys($this->catalog->all()), ...array_keys(self::STANDALONE)];
    }

    public function title(string $slug): ?string
    {
        return self::STANDALONE[$slug]['title'] ?? $this->catalog->find($slug)['title'] ?? null;
    }

    /**
     * Everything the template needs, or null for an unknown slug.
     *
     * @return array<string, mixed>|null
     */
    public function for(string $slug): ?array
    {
        $service = self::STANDALONE[$slug] ?? (($catalog = $this->catalog->find($slug)) !== null ? $catalog + (self::SERVICES[$slug] ?? []) : null);
        if ($service === null) {
            return null;
        }

        $group = self::GROUPS[$service['group']] ?? self::GROUPS['business'];
        $countries = $this->countries($service['countries'] ?? null);
        $headline = isset($service['headline_count']) && $countries->count() > 1
            ? __($service['headline_count'], ['count' => $countries->count()])
            : __($service['headline'] ?? $service['title']);
        $popular = $service['popular'] ?? ($countries->isNotEmpty() ? $countries->take(3)->implode(' · ') : null);
        $fee = $this->facts->serviceFee($slug);

        return [
            'slug' => $slug,
            'quote_service' => $service['quote'] ?? $slug,
            'title' => __($service['title']),
            'headline' => $headline,
            'intro' => __($service['intro'] ?? $service['summary'] ?? ''),
            'group' => $service['group'],
            'icon' => $service['icon'] ?? 'globe',
            'capability' => $service['capability'] ?? null,
            'included' => array_map('__', $service['included'] ?? $service['points'] ?? []),
            'steps' => array_map(static fn (array $step): array => [__($step[0]), __($step[1])], $group['steps']),
            'documents' => array_map('__', $service['documents'] ?? $group['documents']),
            'disclaimer' => __($service['disclaimer'] ?? $group['disclaimer']),
            'topic_label' => __($service['topic_label'] ?? $group['topic_label']),
            'topics' => $countries->all(),
            'facts' => array_values(array_filter([
                $this->facts->get('reply_time') !== null ? ['label' => __('Reply time'), 'value' => $this->facts->get('reply_time')] : null,
                ['label' => __('Service fee'), 'value' => $fee !== null ? __('From BDT :amount', ['amount' => $fee]) : __('Free quote')],
                $popular !== null ? ['label' => __('Popular'), 'value' => $popular] : null,
                ['label' => __('Pay'), 'value' => __($group['pay'])],
            ])),
            'related' => $this->related($slug, $service),
            'cms' => $service['cms'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $service
     * @return list<array{title: string, url: string}>
     */
    private function related(string $slug, array $service): array
    {
        $slugs = $service['related'] ?? collect($this->catalog->all())
            ->filter(fn (array $item, string $key): bool => $key !== $slug && $item['group'] === $service['group'])
            ->keys()->take(3)->all();

        $related = [];
        foreach ($slugs as $relatedSlug) {
            $url = $this->url($relatedSlug);
            if ($url !== null) {
                $related[] = ['title' => __((string) $this->title($relatedSlug)), 'url' => $url];
            }
        }

        return array_slice($related, 0, 3);
    }

    public function url(string $slug): ?string
    {
        if (isset(self::STANDALONE[$slug])) {
            return route(self::STANDALONE[$slug]['route']);
        }

        return $this->catalog->find($slug) !== null ? route('services.show', $slug) : null;
    }

    /**
     * @return Collection<int, string>
     */
    private function countries(?string $service): Collection
    {
        if ($service === null) {
            return collect();
        }

        try {
            return ServiceDestination::query()->where('service', $service)->where('is_active', true)
                ->with('country:id,name,iso2')->orderBy('sort_order')->get()
                // The UAE is listed as Dubai, as in the requirements document.
                ->map(fn (ServiceDestination $destination): ?string => match (true) {
                    $destination->country === null => null,
                    $destination->country->iso2 === 'AE' => __('Dubai (United Arab Emirates)'),
                    default => __($destination->country->name),
                })->filter()->values();
        } catch (Throwable) {
            return collect();
        }
    }
}
