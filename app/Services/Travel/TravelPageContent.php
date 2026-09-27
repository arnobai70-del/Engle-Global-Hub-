<?php

namespace App\Services\Travel;

use App\Services\SettingService;

class TravelPageContent
{
    public function __construct(private readonly SettingService $settings) {}

    /** @return array<string, string> */
    public function for(string $page): array
    {
        $defaults = $this->defaults()[$page] ?? [];
        $stored = $this->settings->group('travel_pages');
        $prefix = $page.'_';

        foreach ($defaults as $key => $value) {
            $storedValue = $stored[$prefix.$key] ?? null;
            if (is_string($storedValue) && trim($storedValue) !== '') {
                $defaults[$key] = $storedValue;
            }
        }

        return $defaults;
    }

    /** @return array<string, array<string, string>> */
    public function defaults(): array
    {
        return [
            'hotels' => [
                'meta_title' => 'Hotels & Stays',
                'meta_description' => 'Search live hotel inventory when connected, or explore a clearly labelled demo preview from Eagle Global Hub.',
                'hero_eyebrow' => 'HOTELS & STAYS',
                'hero_title' => 'Find the right stay for every journey',
                'hero_subtitle' => 'Search hotels by destination, dates and guests. When live supplier inventory is unavailable, clearly labelled sample content previews the same booking experience.',
                'featured_title' => 'Popular stays',
                'destinations_title' => 'Featured hotel destinations',
                'cta_label' => 'Explore hotels',
            ],
            'tours' => [
                'meta_title' => 'Tours & Activities',
                'meta_description' => 'Discover tours and activities with live provider results when connected and clearly labelled demo previews otherwise.',
                'hero_eyebrow' => 'TOURS & ACTIVITIES',
                'hero_title' => 'Turn every destination into an experience',
                'hero_subtitle' => 'Browse city tours, culture, adventure and family activities in the same layout used for live provider results.',
                'featured_title' => 'Popular experiences',
                'destinations_title' => 'Explore by experience',
                'cta_label' => 'Explore activities',
            ],
            'visa' => [
                'meta_title' => 'Visa Assistance',
                'meta_description' => 'Visa information and document-preparation guidance. Approval is always decided by the relevant government authority.',
                'hero_eyebrow' => 'VISA ASSISTANCE',
                'hero_title' => 'Prepare for your visa journey with confidence',
                'hero_subtitle' => 'Check travel-document information when a provider is connected, and use our clearly labelled demo guidance to understand the process beforehand.',
                'featured_title' => 'Visa assistance services',
                'destinations_title' => 'How the process works',
                'cta_label' => 'Check visa requirements',
            ],
            'work_visa' => [
                'meta_title' => 'Work Visa Processing',
                'meta_description' => 'Work visa preparation support for skilled worker, employer-sponsored and work-permit routes. No visa outcome or sponsorship is guaranteed.',
                'hero_eyebrow' => 'WORK VISA PROCESSING',
                'hero_title' => 'Build your global career plan with a clear process',
                'hero_subtitle' => 'Professional preparation support for work permits and employment visa routes. Eligibility and final decisions remain with employers and destination authorities.',
                'featured_title' => 'Work visa service types',
                'destinations_title' => 'Popular destination previews',
                'cta_label' => 'Start a consultation',
            ],
        ];
    }
}
