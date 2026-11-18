<?php

namespace App\Http\Controllers;

use App\Enums\BusinessService;
use App\Http\Requests\StoreTripEnquiryRequest;
use App\Models\Country;
use App\Models\Destination;
use App\Models\ServiceEnquiry;
use App\Notifications\NewLeadReceived;
use App\Services\Content\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Explore Bangladesh for foreign visitors: published destinations with a category
 * filter, travel essentials and a "Plan my trip" enquiry. Trip enquiries are saved as
 * Foreigner support › Travel assistance service enquiries, linked to the chosen places.
 */
class ExploreBangladeshController extends Controller
{
    public const PER_PAGE = 24;

    public function index(Request $request, PageContent $content): View
    {
        $category = is_string($request->query('category')) && array_key_exists($request->query('category'), Destination::CATEGORIES)
            ? $request->query('category') : null;

        $destinations = Destination::query()->orderByDesc('is_featured')->published()->inBangladesh()
            ->when($category !== null, fn ($query) => $query->where('category', $category))
            ->paginate(self::PER_PAGE, ['id', 'name', 'name_bn', 'slug', 'region', 'category', 'summary', 'summary_bn', 'cover_image_path', 'cover_image_alt', 'is_featured'])
            ->withQueryString();

        return view('explore.index', [
            'destinations' => $destinations,
            'category' => $category,
            'heroDestination' => Destination::query()->published()->inBangladesh()->where('is_featured', true)->whereNotNull('cover_image_path')->first(['id', 'name', 'cover_image_path', 'cover_image_alt']),
            'essentials' => $this->essentials($content),
            ...$this->formData(),
        ]);
    }

    public function show(string $slug): View
    {
        $destination = Destination::query()->published()->inBangladesh()->where('slug', $slug)
            ->with([
                'images',
                'publishedListings',
                'recommendations' => fn ($query) => $query->where('is_published', true)->inBangladesh(),
            ])->firstOrFail();

        return view('explore.show', [
            'destination' => $destination,
            'listings' => $destination->publishedListings->groupBy('type'),
            ...$this->formData(),
        ]);
    }

    public function enquire(StoreTripEnquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $places = Destination::query()->whereKey($data['destination_ids'] ?? [])->orderBy('name')->get(['id', 'name']);

        $enquiry = ServiceEnquiry::query()->create([
            'reference' => $this->newReference(),
            'service' => BusinessService::ForeignerSupport,
            'category' => 'travel_assistance',
            'user_id' => $request->user()?->getKey(),
            'destination_country_id' => Country::query()->where('iso2', 'BD')->value('id'),
            'route_or_destination' => $places->isNotEmpty() ? Str::limit('Bangladesh: '.$places->pluck('name')->implode(', '), 197) : 'Bangladesh',
            'travel_date' => $data['travel_date'] ?? null,
            'return_date' => $data['return_date'] ?? null,
            'travellers' => (int) $data['travellers'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $this->message($data, $request->residenceCountry()),
            'consented_at' => now(),
        ]);
        $enquiry->destinations()->sync($places->pluck('id')->all());
        NewLeadReceived::notifyTeam($enquiry, 'Explore Bangladesh: plan my trip');

        return redirect()->to(url()->previous(route('explore-bangladesh.index')).'#plan-trip')->with('lead_reference', $enquiry->reference);
    }

    /**
     * Data for the "Plan my trip" form.
     *
     * @return array{tripPlaces: Collection<int, Destination>, countries: Collection<int, Country>}
     */
    private function formData(): array
    {
        return [
            'tripPlaces' => Destination::query()->published()->inBangladesh()->get(['id', 'name', 'name_bn']),
            'countries' => Country::query()->where('is_active', true)->where('iso2', '!=', 'BD')->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Travel essentials: the published page section "explore-essentials" ("- Title: text" per line)
     * when an admin has published one, otherwise the built-in text below.
     *
     * @return list<array{title: string, text: string}>
     */
    private function essentials(PageContent $content): array
    {
        $edited = $content->points('explore-essentials');

        return $edited !== [] ? $edited : array_map(fn (array $item): array => ['title' => __($item[0]), 'text' => __($item[1])], self::DEFAULT_ESSENTIALS);
    }

    /**
     * Built-in travel essentials. No visa rules are stated: visitors are sent to the official sources.
     */
    public const DEFAULT_ESSENTIALS = [
        ['Visa', 'Check the official Bangladesh e-visa and visa-on-arrival rules for your nationality before you travel. We can explain the process and help with your documents.'],
        ['Currency', 'The currency is the Bangladeshi Taka (BDT). Exchange money at banks or authorised money changers, including at the airport.'],
        ['Cash and cards', 'Cards are accepted in larger hotels, restaurants and shops in the cities. Carry cash for markets, local transport and rural areas.'],
        ['SIM cards and mobile data', 'Local SIM cards with mobile data are sold at the airport and in operator shops; bring your passport to register one.'],
        ['Getting around', 'Domestic flights connect Dhaka with the main cities. Trains and long-distance buses are good value, and a hired car with a driver is the most comfortable option.'],
        ['Best season', 'Roughly November to February is the cool, dry season and the most comfortable time to travel. The monsoon brings heavy rain from about June to September.'],
        ['Dress and etiquette', 'Dress modestly, especially at mosques, temples and in rural areas. Remove your shoes where asked and ask before photographing people.'],
        ['Safety and emergencies', 'Call 999, the national emergency number, for police, fire or ambulance. Keep a copy of your passport and visa, and share your plans with your hotel or guide.'],
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    private function message(array $data, ?Country $residence): string
    {
        $needs = collect($data['needs'] ?? [])->map(fn (string $need): string => StoreTripEnquiryRequest::NEEDS[$need])->implode(', ');

        return trim(implode("\n", array_filter([
            'Plan my trip (Explore Bangladesh)',
            $residence !== null ? 'Country of residence: '.$residence->name : null,
            $needs !== '' ? 'Needs: '.$needs : null,
            filled($data['message'] ?? null) ? "\n".$data['message'] : null,
        ])));
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SQ-'.Str::upper(Str::random(10));
        } while (ServiceEnquiry::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
