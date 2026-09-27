<?php

namespace App\Services;

use App\Models\HomepageBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomepageContentService
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    /** @return array<string, mixed> */
    public function siteChrome(): array
    {
        $general = $this->settings->group('general');
        $contact = $this->settings->group('contact');
        $social = $this->settings->group('social');

        return [
            'siteName' => $general['site_name'] ?? 'Eagle Global Hub LTD',
            'siteTagline' => $general['site_tagline'] ?? 'Travel & Visa Services',
            'siteLogo' => $this->mediaUrl($general['logo_path'] ?? null),
            'siteFooterDescription' => $general['footer_description']
                ?? 'Travel, visa and booking services managed from one secure account.',
            'siteContact' => [
                'phone' => $contact['phone'] ?? null,
                'email' => $contact['email'] ?? null,
                'address' => $contact['address'] ?? null,
                'hours' => $contact['hours'] ?? null,
            ],
            'siteSocial' => array_filter([
                'facebook' => $social['facebook_url'] ?? null,
                'instagram' => $social['instagram_url'] ?? null,
                'linkedin' => $social['linkedin_url'] ?? null,
                'youtube' => $social['youtube_url'] ?? null,
            ]),
            'footerLinks' => Schema::hasTable('homepage_blocks')
                ? $this->mapBlocks(
                    HomepageBlock::query()->active()->where('section', 'footer_link')->orderBy('sort_order')->get(),
                    'footer_link',
                )
                : [],
        ];
    }

    /** @return array<string, mixed> */
    public function home(): array
    {
        $homepage = array_merge($this->defaultSettings(), $this->settings->group('homepage'));
        $app = array_merge($this->defaultAppSettings(), $this->settings->group('app'));
        $seo = array_merge($this->defaultSeo(), $this->settings->group('seo'));

        $homepage['hero_image'] = $this->mediaUrl(
            $homepage['hero_image'] ?? null,
            $this->defaultSettings()['hero_image'],
        );
        $homepage['journey_background'] = $this->mediaUrl(
            $homepage['journey_background'] ?? null,
            $this->defaultSettings()['journey_background'],
        );

        $blocks = Schema::hasTable('homepage_blocks')
            ? HomepageBlock::query()
                ->orderBy('section')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('section')
            : collect();

        return [
            'settings' => array_merge($homepage, $app),
            'seo' => [
                'title' => $seo['home_meta_title'],
                'description' => $seo['home_meta_description'],
                'canonical' => $seo['home_canonical_url'] ?? null,
                'og_title' => $seo['home_og_title'] ?? $seo['home_meta_title'],
                'og_description' => $seo['home_og_description'] ?? $seo['home_meta_description'],
                'og_image' => $this->mediaUrl($seo['home_og_image'] ?? null, $homepage['hero_image']),
                'robots' => $seo['home_robots'] ?? 'index,follow',
                'twitter_card' => $seo['home_twitter_card'] ?? 'summary_large_image',
            ],
            'assurances' => $this->sectionOrDefaults($blocks, 'assurance'),
            'promotions' => $this->sectionOrDefaults($blocks, 'promotion'),
            'services' => $this->sectionOrDefaults($blocks, 'service'),
            'destinations' => $this->sectionOrDefaults($blocks, 'destination'),
            'service_panels' => $this->sectionOrDefaults($blocks, 'service_panel'),
            'benefits' => $this->sectionOrDefaults($blocks, 'benefit'),
            'testimonials' => $this->mapBlocks(
                $blocks->get('testimonial', collect())->where('is_active', true),
                'testimonial',
            ),
            'app_features' => $this->sectionOrDefaults($blocks, 'app_feature'),
            'footer_links' => $this->mapBlocks(
                $blocks->get('footer_link', collect())->where('is_active', true),
                'footer_link',
            ),
        ];
    }

    public function mediaUrl(?string $path, ?string $fallback = null): ?string
    {
        if ($path === null || trim($path) === '') {
            return $fallback;
        }

        $path = trim($path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return $fallback;
    }

    /** @return array<string, mixed> */
    private function defaultSettings(): array
    {
        return [
            'hero_eyebrow' => 'EXPLORE · BOOK · TRAVEL · GROW',
            'hero_title' => 'Travel the World with',
            'hero_accent' => 'Eagle Global Hub',
            'hero_subtitle' => 'Flights, Hotels, Tours, Visa, Work Visa Processing & More — search and manage your journey from one secure account.',
            'hero_side_text' => "Discover\nExplore\nWork\nStudy\nTravel Beyond\nBoundaries",
            'hero_image' => 'https://images.unsplash.com/photo-1505228395891-9a51e7e86bf6?auto=format&fit=crop&w=2560&q=85',
            'services_title' => 'Our Services',
            'services_subtitle' => 'Everything you need for a perfect journey',
            'destinations_title' => 'Popular Destinations',
            'destinations_subtitle' => 'Discover the most loved destinations around the world',
            'benefits_title' => 'Why Choose Eagle Global Hub?',
            'benefits_subtitle' => 'Clear provider availability, secure account steps and visible booking status.',
            'testimonials_title' => 'What Our Customers Say',
            'testimonials_subtitle' => 'Only approved customer testimonials are published here.',
            'newsletter_title' => "Let's Make Your Next Journey Amazing",
            'newsletter_subtitle' => 'Travel updates and useful offers, sent only when you choose to subscribe.',
            'journey_title' => "Let's Make Your Next Journey Amazing",
            'journey_copy' => 'Search available travel services, manage bookings in your account, or subscribe for travel updates.',
            'journey_background' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=2000&q=85',
        ];
    }

    /** @return array<string, mixed> */
    private function defaultAppSettings(): array
    {
        return [
            'app_title' => 'Download Our Mobile App',
            'app_subtitle' => 'Use the same account and booking services in your browser today. Store links appear when published builds are configured.',
            'google_play_url' => null,
            'app_store_url' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function defaultSeo(): array
    {
        return [
            'home_meta_title' => 'Eagle Global Hub | Flights, Hotels, Tours & Visa Services',
            'home_meta_description' => 'Search flights and explore hotels, tours, visa and work visa services with Eagle Global Hub.',
            'home_canonical_url' => null,
            'home_og_title' => null,
            'home_og_description' => null,
            'home_og_image' => null,
            'home_robots' => 'index,follow',
            'home_twitter_card' => 'summary_large_image',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultBlocks(string $section): array
    {
        return match ($section) {
            'assurance' => [
                ['key' => 'compare-fares', 'title' => 'Compare fares', 'subtitle' => 'Every provider offer, side by side', 'icon' => $this->iconSvg('list')],
                ['key' => 'live-revalidation', 'title' => 'Live revalidation', 'subtitle' => 'Selected fares are rechecked', 'icon' => $this->iconSvg('check')],
                ['key' => 'secure-account', 'title' => 'Secure account steps', 'subtitle' => 'Search and booking stay protected', 'icon' => $this->iconSvg('shield')],
                ['key' => 'visible-status', 'title' => 'Status you can check', 'subtitle' => 'Booking status remains visible', 'icon' => $this->iconSvg('document')],
                ['key' => 'honest-availability', 'title' => 'Honest availability', 'subtitle' => 'Services appear only when configured', 'icon' => $this->iconSvg('globe')],
            ],
            'promotion' => [
                ['key' => 'flight-offer', 'badge' => 'Flights', 'title' => 'FLAT 20% OFF on International Flights', 'copy' => 'Promotional pricing applies only when returned by the configured flight provider and valid under its fare rules.', 'cta' => 'Search Flights', 'feature' => 'flights', 'image' => 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1400&q=85'],
                ['key' => 'hotel-offer', 'badge' => 'Hotels', 'title' => 'Up to 40% OFF on Hotel Bookings', 'copy' => 'Hotel offers appear only when the configured provider returns eligible rates.', 'cta' => 'Book Now', 'feature' => 'hotels', 'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=85'],
                ['key' => 'work-visa-promo', 'badge' => 'Work Visa', 'title' => 'Work Visa Processing', 'copy' => 'Start your global career with work visa information and document guidance.', 'cta' => 'Apply Now', 'work_visa' => true, 'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1400&q=85', 'items' => ['Job Visa', 'Skilled Worker Visa', 'Employer Sponsored Visa', 'Document Assistance']],
            ],
            'service' => [
                ['key' => 'flights', 'title' => 'Flights', 'copy' => 'Domestic & International', 'service' => 'flights', 'icon' => $this->iconSvg('plane')],
                ['key' => 'hotels', 'title' => 'Hotels', 'copy' => 'Best stays worldwide', 'service' => 'hotels', 'icon' => $this->iconSvg('hotel')],
                ['key' => 'tours', 'title' => 'Tours & Activities', 'copy' => 'Explore experiences', 'service' => 'tours', 'icon' => $this->iconSvg('globe')],
                ['key' => 'visa', 'title' => 'Visa Assistance', 'copy' => 'Tourist & Business Visa', 'service' => 'visa', 'icon' => $this->iconSvg('document')],
                ['key' => 'work-visa', 'title' => 'Work Visa Processing', 'copy' => 'Jobs & Work Permits', 'service' => null, 'icon' => $this->iconSvg('briefcase')],
                ['key' => 'holiday-packages', 'title' => 'Holiday Packages', 'copy' => 'Customized tours', 'service' => null, 'icon' => $this->iconSvg('gift')],
                ['key' => 'trains', 'title' => 'Trains', 'copy' => 'Easy train booking', 'service' => null, 'icon' => $this->iconSvg('train')],
                ['key' => 'buses', 'title' => 'Buses', 'copy' => 'Comfortable travel', 'service' => null, 'icon' => $this->iconSvg('bus')],
                ['key' => 'cabs', 'title' => 'Cabs', 'copy' => 'Airport & local rides', 'service' => null, 'icon' => $this->iconSvg('car')],
                ['key' => 'insurance', 'title' => 'Travel Insurance', 'copy' => 'Safe & secure journeys', 'service' => null, 'icon' => $this->iconSvg('shield')],
            ],
            'destination' => [
                ['key' => 'dubai', 'name' => 'Dubai', 'country' => 'UAE', 'tag' => 'Dubai', 'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'bali', 'name' => 'Bali', 'country' => 'Indonesia', 'tag' => 'Bali', 'image' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'bangkok', 'name' => 'Bangkok', 'country' => 'Thailand', 'tag' => 'Bangkok', 'image' => 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'singapore', 'name' => 'Singapore', 'country' => 'Singapore', 'tag' => 'Singapore', 'image' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'maldives', 'name' => 'Maldives', 'country' => 'Maldives', 'tag' => 'Maldives', 'image' => 'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'london', 'name' => 'London', 'country' => 'UK', 'tag' => 'London', 'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'istanbul', 'name' => 'Istanbul', 'country' => 'Türkiye', 'tag' => 'Istanbul', 'image' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?auto=format&fit=crop&w=1000&q=85'],
                ['key' => 'paris', 'name' => 'Paris', 'country' => 'France', 'tag' => 'Paris', 'image' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1000&q=85'],
            ],
            'service_panel' => [
                ['key' => 'visa-services', 'class' => 'is-visa', 'title' => 'Visa Services', 'copy' => 'Visa assistance for supported destinations.', 'items' => ['Tourist Visa', 'Business Visa', 'Transit Visa', 'Visa Requirements Check'], 'cta' => 'Apply for Visa', 'service' => 'visa', 'image' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=85'],
                ['key' => 'work-visa', 'class' => 'is-workvisa', 'title' => 'Work Visa Processing', 'copy' => 'Work visa information and document assistance.', 'items' => ['Job Visa & Work Permits', 'Skilled Worker Visa', 'Employer Sponsored Visa', 'Document Assistance'], 'cta' => 'Apply Now', 'service' => null, 'work_visa' => true, 'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=85'],
                ['key' => 'holiday-packages', 'class' => 'is-holiday', 'title' => 'Holiday Packages', 'copy' => 'Package holidays are shown when a corresponding service is configured.', 'items' => ['Family Packages', 'Honeymoon Packages', 'Adventure Tours', 'Customized Itineraries'], 'cta' => null, 'service' => null, 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85'],
            ],
            'benefit' => [
                ['key' => 'compare', 'title' => 'Fares compared in one place', 'copy' => 'Every offer returned by the configured provider is listed side by side.', 'icon' => $this->iconSvg('list')],
                ['key' => 'revalidate', 'title' => 'Live fare revalidation', 'copy' => 'A selected fare is revalidated before traveller details are collected.', 'icon' => $this->iconSvg('check')],
                ['key' => 'account', 'title' => 'Secure account steps', 'copy' => 'Search, traveller and booking status stay inside the signed-in account.', 'icon' => $this->iconSvg('shield')],
                ['key' => 'availability', 'title' => 'Explicit availability', 'copy' => 'A service is marked available only when its provider is configured.', 'icon' => $this->iconSvg('globe')],
                ['key' => 'status', 'title' => 'Visible booking status', 'copy' => 'Bookings keep their own order and payment status for later review.', 'icon' => $this->iconSvg('document')],
                ['key' => 'credentials', 'title' => 'Credentials stay server-side', 'copy' => 'Provider credentials are never rendered into public pages.', 'icon' => $this->iconSvg('lock')],
            ],
            'app_feature' => [
                ['key' => 'flight-bookings', 'title' => 'Flight Bookings', 'icon' => $this->iconSvg('plane')],
                ['key' => 'hotel-deals', 'title' => 'Hotel Deals', 'icon' => $this->iconSvg('hotel')],
                ['key' => 'visa-services', 'title' => 'Visa Services', 'icon' => $this->iconSvg('document')],
                ['key' => 'booking-status', 'title' => 'Booking Status', 'icon' => $this->iconSvg('list')],
            ],
            default => [],
        };
    }

    private function iconSvg(?string $icon): string
    {
        return match ($icon) {
            'plane' => '<path d="M3 13.5 21 5l-3.5 8.5L21 19z"/><path d="M8.5 12.2 3 13.5"/>',
            'hotel' => '<path d="M4 20V9m0 5h16v6M4 9l8-5 8 5"/><path d="M9.5 14.5v-2h5v2"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9s-1.2 6.5-3.6 9c-2.4-2.5-3.6-5.5-3.6-9S9.6 5.5 12 3z"/>',
            'document' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 13h6M9 16.5h4"/>',
            'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2M3 12h18"/>',
            'gift' => '<rect x="3" y="9" width="18" height="11" rx="2"/><path d="M12 9v11M3 13h18M12 9c-3 0-5-1.1-5-3 0-1.2.9-2 2-2 1.7 0 3 2.1 3 5Zm0 0c3 0 5-1.1 5-3 0-1.2-.9-2-2-2-1.7 0-3 2.1-3 5Z"/>',
            'train' => '<rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 10h14M9 21l-1.5-3M15 21l1.5-3M9.5 13.5h.01M14.5 13.5h.01"/>',
            'bus' => '<rect x="4" y="4" width="16" height="13" rx="2.5"/><path d="M4 11h16M8 20v-3M16 20v-3M7.5 14h.01M16.5 14h.01"/>',
            'car' => '<path d="M5 16.5h14M6.5 16.5V12l1.8-3.6A1.6 1.6 0 0 1 9.7 7.4h4.6a1.6 1.6 0 0 1 1.4 1l1.8 3.6v4.5"/><path d="M5 16.5v2.2h2.5v-2.2M16.5 16.5v2.2H19v-2.2M8.5 12.5h7"/>',
            'shield' => '<path d="M12 3 4.5 6.6v4.9c0 4.6 3.1 7.6 7.5 8.5 4.4-.9 7.5-3.9 7.5-8.5V6.6z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
            'star' => '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 21c.7-4.3 3.2-6.5 7.5-6.5s6.8 2.2 7.5 6.5"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.3 2.3 4.7-5"/>',
            'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
            'headset' => '<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h3v6H5a1 1 0 0 1-1-1zm16 0h-3v6h2a1 1 0 0 0 1-1zM17 19c-.7 1.3-2.3 2-5 2"/>',
            'lock' => '<rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7.5a4 4 0 0 1 8 0V10"/>',
            'list' => '<path d="M9 6h10M9 12h10M9 18h10"/><path d="m4 6 1.4 1.4L8 5M4 12l1.4 1.4L8 11M4 18l1.4 1.4L8 17"/>',
            default => '<circle cx="12" cy="12" r="9"/>',
        };
    }

    /** @return array<int, array<string, mixed>> */
    private function sectionOrDefaults(Collection $blocks, string $section): array
    {
        $sectionBlocks = $blocks->get($section, collect());

        if ($sectionBlocks->isEmpty()) {
            return $this->defaultBlocks($section);
        }

        return $this->mapBlocks(
            $sectionBlocks->where('is_active', true),
            $section,
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function mapBlocks(Collection $blocks, string $section): array
    {
        return $blocks->map(fn (HomepageBlock $block): array => $this->mapBlock($block, $section))->values()->all();
    }

    /** @return array<string, mixed> */
    private function mapBlock(HomepageBlock $block, string $section): array
    {
        $meta = is_array($block->meta) ? $block->meta : [];
        $base = [
            'id' => $block->id,
            'key' => $block->key,
            'title' => $block->title,
            'subtitle' => $block->subtitle,
            'copy' => $block->body,
            'body' => $block->body,
            'image' => $this->mediaUrl($block->image_path),
            'image_alt' => $block->image_alt,
            'icon' => $this->iconSvg($block->icon),
            'url' => $block->url,
            'cta' => $block->cta_label,
        ];
        $mapped = array_merge($base, $meta);

        return match ($section) {
            'destination' => array_merge($mapped, [
                'name' => $block->title,
                'country' => $block->subtitle,
                'tag' => $meta['tag'] ?? $block->title,
            ]),
            'testimonial' => array_merge($mapped, [
                'name' => $block->title,
                'role' => $block->subtitle,
                'quote' => $block->body,
            ]),
            default => $mapped,
        };
    }
}
