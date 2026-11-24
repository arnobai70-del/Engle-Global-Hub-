<?php

namespace App\Services\Content;

use App\Enums\BusinessService;
use App\Models\MenuItem;
use App\Models\ServiceDestination;
use App\Services\Consultancy\StudyCountries;
use App\Services\Navigation\MenuService;
use App\Services\Navigation\MenuTargets;
use Illuminate\Support\Collection;
use Throwable;

/**
 * The homepage service cards. Titles, icons and links come from Admin › Menus
 * (homepage tiles); a card's description is the one typed in admin, or the
 * built-in text for the original tiles. A card that repeats an earlier card's
 * link or title is shown only once, and admin warns about both problems.
 */
class HomepageServiceCards
{
    public function __construct(private MenuService $menus, private MenuTargets $targets) {}

    /**
     * @return list<array{key: ?string, title: string, url: string, icon: string, new_tab: bool, body: string, cta: string}>
     */
    public function cards(): array
    {
        $tiles = $this->menus->tree('home_tiles');
        $cards = $tiles !== null
            ? array_map(fn (array $tile): array => [
                'key' => $tile['key'] ?? null,
                'title' => $tile['label'],
                'url' => (string) $tile['url'],
                'icon' => $tile['icon'] ?? 'globe',
                'new_tab' => $tile['new_tab'],
                'description' => $tile['description'] ?? null,
            ], $tiles)
            : $this->defaultTiles();

        $seenLinks = [];
        $seenTitles = [];
        $unique = [];
        foreach ($cards as $card) {
            $link = $this->normalisedLink($card['url']);
            $title = $this->normalisedTitle($card['title']);
            if (isset($seenLinks[$link]) || isset($seenTitles[$title])) {
                continue;
            }
            $seenLinks[$link] = true;
            $seenTitles[$title] = true;
            [$builtInBody, $cta] = $this->builtInText()[$card['key'] ?? ''] ?? ['', __('Learn more')];
            $unique[] = [
                'key' => $card['key'],
                'title' => $card['title'],
                'url' => $card['url'],
                'icon' => $card['icon'],
                'new_tab' => $card['new_tab'],
                'body' => $card['description'] ?? $builtInBody,
                'cta' => $cta,
            ];
        }

        // Admin › Services & Destinations: optional, automatically generated study
        // country cards. The linked page uses the existing published destination
        // when complete; otherwise it safely opens the Student Visa enquiry form.
        // Country cards are never invented for inactive or unselected countries.
        try {
            $countries = ServiceDestination::query()
                ->with('country')
                ->where('service', BusinessService::StudentVisa->value)
                ->where('is_active', true)
                ->where('show_on_homepage', true)
                ->orderBy('sort_order')->orderBy('id')->get();

            foreach ($countries as $destination) {
                $country = $destination->country;
                if ($country === null || ! $country->is_active) {
                    continue;
                }

                $url = $this->targets->studyCountryUrl($country);
                $title = __('Study in :country', ['country' => __($country->name)]);
                $link = $this->normalisedLink($url);
                $name = $this->normalisedTitle($title);
                if (isset($seenLinks[$link]) || isset($seenTitles[$name])) {
                    continue;
                }

                $seenLinks[$link] = true;
                $seenTitles[$name] = true;
                $unique[] = [
                    'key' => 'study-country-'.strtolower($country->iso2),
                    'title' => $title,
                    'url' => $url,
                    'icon' => 'cap',
                    'new_tab' => false,
                    'body' => __('Course, admission and student visa guidance for :country. Ask us about current requirements.', ['country' => __($country->name)]),
                    'cta' => __('Explore options'),
                ];
            }
        } catch (Throwable) {
            // The homepage still works during a rolling release before migration.
        }

        return $unique;
    }

    /**
     * Problems an admin should fix, per homepage tile id.
     *
     * @param  Collection<int, MenuItem>  $tiles  homepage tiles in display order
     * @return array<int, list<string>>
     */
    public function adminWarnings(Collection $tiles): array
    {
        $warnings = [];
        $seenLinks = [];
        $seenTitles = [];
        foreach ($tiles as $tile) {
            $problems = [];
            if (blank($tile->description_en) && ($this->builtInText()[(string) $tile->key][0] ?? '') === '') {
                $problems[] = 'No description: the card shows only its title. Add a short description.';
            }
            $link = $this->normalisedLink((string) $this->targets->url($tile->target, $tile->fragment));
            $title = $this->normalisedTitle((string) $tile->label_en);
            if ($link !== '' && isset($seenLinks[$link])) {
                $problems[] = 'Same link as "'.$seenLinks[$link].'": only the first card is shown.';
            } elseif (isset($seenTitles[$title])) {
                $problems[] = 'Same title as an earlier card: only the first card is shown.';
            }
            $seenLinks[$link] ??= (string) $tile->label_en;
            $seenTitles[$title] = true;
            if ($problems !== []) {
                $warnings[$tile->id] = $problems;
            }
        }

        return $warnings;
    }

    private function normalisedLink(string $url): string
    {
        $url = strtolower(trim($url));
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        $fragment = (string) (parse_url($url, PHP_URL_FRAGMENT) ?? '');
        $host = (string) (parse_url($url, PHP_URL_HOST) ?? '');

        return $url === '' ? '' : preg_replace('/^www\./', '', $host).'/'.trim($path, '/').($fragment !== '' ? '#'.$fragment : '');
    }

    private function normalisedTitle(string $title): string
    {
        return (string) preg_replace('/\s+/u', ' ', mb_strtolower(trim($title)));
    }

    /**
     * Built-in descriptions and button text for the original tiles.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function builtInText(): array
    {
        return [
            'tile-malaysia' => [__('Universities and colleges in Malaysia: course shortlist, admission application and document check.'), __('Start here')],
            'tile-student-pass-my' => [__('Your EMGS Student Pass and eVAL application, prepared and followed with you.'), __('Learn more')],
            'tile-singapore' => [__('Diplomas and degrees in Singapore, with Student\'s Pass (ICA) guidance.'), __('Explore Singapore')],
            'tile-visa' => [__('Tourist, business and student visa guidance with a document checklist.'), __('Ask about a visa')],
            'tile-flights' => [__('Student fares and group bookings. Send your dates, we reply with options.'), __('Get a fare quote')],
            'tile-housing' => [__('Student hostels, shared rooms and airport arrival guidance.'), __('Ask about housing')],
            'tile-tours' => [__('Holiday and group tour packages in Bangladesh and abroad.'), __('Get a package')],
            // Keys of the first release's tiles, still present on older databases.
            'tile-study' => [__('Diplomas and degrees in Singapore, with Student\'s Pass (ICA) guidance.'), __('Explore Singapore')],
            'tile-students-pass' => [__('Singapore Student\'s Pass (ICA) guidance and a document checklist.'), __('Learn more')],
            'tile-hotels' => [__('Hotels, student hostels and shared rooms, confirmed with you before booking.'), __('Ask about housing')],
            'tile-explore' => [__('Historical sites, beaches, hills and forests, with help planning your trip.'), __('Explore Bangladesh')],
        ];
    }

    /**
     * Cards used before any homepage tile is created in admin.
     *
     * @return list<array{key: string, title: string, url: string, icon: string, new_tab: bool, description: null}>
     */
    private function defaultTiles(): array
    {
        $studyUrl = route('consultancy.malaysia');

        return [
            ['key' => 'tile-malaysia', 'title' => __('Study in Malaysia'), 'url' => $studyUrl, 'icon' => 'cap', 'new_tab' => false, 'description' => null],
            ['key' => 'tile-student-pass-my', 'title' => __('Student Pass (EMGS)'), 'url' => $studyUrl.'#student-pass', 'icon' => 'id', 'new_tab' => false, 'description' => null],
            ['key' => 'tile-visa', 'title' => __('Visa services'), 'url' => route('services.show', 'tourist-visa'), 'icon' => 'passport', 'new_tab' => false, 'description' => null],
            ['key' => 'tile-flights', 'title' => __('Air tickets'), 'url' => route('services.show', 'air-ticket'), 'icon' => 'plane', 'new_tab' => false, 'description' => null],
            ['key' => 'tile-housing', 'title' => __('Housing & arrival'), 'url' => route('hotels.index'), 'icon' => 'bed', 'new_tab' => false, 'description' => null],
        ];
    }
}
