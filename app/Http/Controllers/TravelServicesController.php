<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Feature\FeatureManager;
use App\Services\Integrations\ProviderStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The hub for every travel, visa and transport service. Each card links to the
 * service's own page and shows the real provider state from {@see ProviderStatus}.
 */
class TravelServicesController extends Controller
{
    public function __invoke(Request $request, ProviderStatus $status, FeatureManager $features): View
    {
        $user = $request->user();
        $user = $user instanceof User ? $user : null;
        $visible = fn (string $feature): bool => $features->isVisibleTo($feature, $user);

        $card = fn (string $key, string $title, string $icon, string $summary, string $state, string $href, string $cta): array => compact('key', 'title', 'icon', 'summary', 'state', 'href', 'cta');
        $enquiry = ProviderStatus::ENQUIRY;

        $groups = [
            'Flights, hotels & tours' => array_values(array_filter([
                $visible('flights') ? $card('flights', 'Air Ticket', 'plane', 'Search fares when the flight provider is live, or ask our team for a quote.', $status->of('flights', $user), route('services.show', 'air-ticket'), 'View air tickets') : null,
                $visible('flights') ? $card('business-class', 'Business Class', 'seat', 'Business class fares quoted personally by our team.', $enquiry, route('services.show', 'business-class'), 'View business class') : null,
                $visible('hotels') ? $card('hotels', 'Hotels & Homes', 'bed', 'Hotel availability and booking from a live supplier once connected.', $status->of('hotels', $user), route('hotels.index'), 'View hotels') : null,
                $visible('tours') ? $card('tours', 'Tours', 'mountain', 'City tours, culture, adventure and family activities.', $status->of('tours', $user), route('tours.index'), 'View tours') : null,
                $card('holiday-packages', 'Holiday Packages', 'sun', 'Flights, hotels and tours planned together.', $enquiry, route('services.show', 'holiday-packages'), 'View holiday packages'),
                $card('group-tours', 'Group Tours', 'group', 'Tours for friends, families, students and companies.', $enquiry, route('services.show', 'group-tours'), 'View group tours'),
                $card('private-tours', 'Private Tours', 'mountain', 'Your own schedule, transport and guide.', $enquiry, route('services.show', 'private-tours'), 'View private tours'),
            ])),
            'Visas' => array_values(array_filter([
                $card('tourist-visa', 'Tourist Visa', 'passport', 'Checklist and document preparation for 21 destinations.', $enquiry, route('services.show', 'tourist-visa'), 'View tourist visa'),
                $card('business-visa', 'Business Visa', 'briefcase', 'Preparation support for business visits and conferences.', $enquiry, route('services.show', 'business-visa'), 'View business visa'),
                $visible('visa') ? $card('work-visa', 'Work Visa', 'visa', 'Work permit and job visa information with a consultation.', $enquiry, route('work-visa.index'), 'View work visa') : null,
                $card('student-visa', 'Student Visa', 'cap', 'Study abroad consultancy, with Malaysia as our main destination, then Singapore.', $enquiry, route('services.show', 'student-visa'), 'View student visa'),
                $visible('visa') ? $card('visa-info', 'Visa information', 'globe', 'Live, trip-specific visa requirements need a visa information provider.', $status->of('visa', $user), route('visa.index'), 'View visa information') : null,
            ])),
            'Transport' => [
                $card('trains', 'Trains', 'train', 'Train ticket support for your route and dates.', $enquiry, route('services.show', 'trains'), 'View trains'),
                $card('bus', 'Bus', 'bus', 'Bus tickets for local and cross-border routes.', $enquiry, route('services.show', 'bus'), 'View bus'),
                $card('cars', 'Cars', 'car', 'Car hire and airport transfers with a driver.', $enquiry, route('services.show', 'cars'), 'View cars'),
                $card('bike', 'Bike', 'bike', 'Bike rental support for local trips.', $enquiry, route('services.show', 'bike'), 'View bike'),
            ],
        ];

        return view('travel.index', [
            'groups' => $groups,
            'visaInfoVisible' => $visible('visa'),
        ]);
    }
}
