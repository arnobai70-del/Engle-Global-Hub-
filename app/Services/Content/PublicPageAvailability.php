<?php

namespace App\Services\Content;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Course;
use App\Models\Destination;
use App\Models\EligibilityRule;
use App\Models\ForumThread;
use App\Models\Institution;
use App\Models\Offer;
use App\Models\Review;
use App\Models\RewardProgram;
use App\Services\Consultancy\MalaysiaCostCalculator;
use App\Services\Consultancy\StudyCountries;
use App\Services\SettingService;
use Throwable;

/**
 * Whether a public listing page has anything published yet. Pages without content
 * stay reachable by URL but are left out of menus, the footer and the sitemap;
 * they come back automatically once an admin publishes the first item.
 */
class PublicPageAvailability
{
    /** Route names this service decides about. Every other route is always available. */
    public const ROUTES = [
        'company.index', 'success-stories.index', 'offers.index', 'forums.index', 'reviews.index',
        'rewards.index', 'restaurants.index', 'inspiration.index', 'map', 'explore-bangladesh.index',
        'consultancy.guides.index', 'consultancy.institutions.index', 'mobile-app',
        'consultancy.malaysia.guides.index', 'consultancy.malaysia.institutions.index',
        'consultancy.malaysia.eligibility', 'consultancy.malaysia.cost-calculator', 'consultancy.malaysia.courses',
    ];

    public function __construct(private SettingService $settings, private StudyCountries $countries) {}

    public function isAvailable(string $routeName): bool
    {
        if (! in_array($routeName, self::ROUTES, true)) {
            return true;
        }

        // Remembered for the current request only, so a page appears as soon as content is published.
        $request = request();
        $cache = (array) $request->attributes->get('egh.page_availability', []);
        if (! array_key_exists($routeName, $cache)) {
            $cache[$routeName] = $this->check($routeName);
            $request->attributes->set('egh.page_availability', $cache);
        }

        return $cache[$routeName];
    }

    private function check(string $routeName): bool
    {
        try {
            return match ($routeName) {
                'company.index' => $this->hasPublished(ContentType::CompanyProfile),
                'success-stories.index' => $this->hasPublished(ContentType::SuccessStory),
                'restaurants.index' => $this->hasPublished(ContentType::Restaurant),
                'inspiration.index' => $this->hasPublished(ContentType::Inspiration),
                'consultancy.guides.index' => $this->countries->scopeGuides(ContentEntry::query()->published(ContentType::Guide), 'SG')->exists(),
                'consultancy.malaysia.guides.index' => $this->countries->scopeGuides(ContentEntry::query()->published(ContentType::Guide), 'MY')->exists(),
                'consultancy.malaysia.institutions.index' => Institution::query()->publiclyListed()->whereHas('country', fn ($query) => $query->where('iso2', 'MY'))->exists(),
                'offers.index' => Offer::query()->current()->exists(),
                'reviews.index' => Review::query()->where('status', Review::STATUS_APPROVED)->exists(),
                'rewards.index' => RewardProgram::query()->live()->exists(),
                'forums.index' => ForumThread::query()->whereHas('category', fn ($query) => $query->where('is_active', true))->exists(),
                'explore-bangladesh.index' => Destination::query()->published()->exists(),
                'consultancy.institutions.index' => Institution::query()->publiclyListed()->where(fn ($query) => $query->whereNull('country_id')->orWhereHas('country', fn ($country) => $country->where('iso2', '!=', 'MY')))->exists(),
                'map' => Destination::query()->published()->exists()
                    || ContentEntry::query()->where('status', ContentEntry::STATUS_PUBLISHED)->whereNotNull('latitude')->whereNotNull('longitude')->exists(),
                'mobile-app' => $this->hasAppStoreLink(),
                'consultancy.malaysia.eligibility' => EligibilityRule::query()->where('is_active', true)->exists(),
                'consultancy.malaysia.cost-calculator' => app(MalaysiaCostCalculator::class)->isAvailable(),
                'consultancy.malaysia.courses' => Course::query()->where('is_active', true)->whereIn('institution_id', Institution::query()->publiclyListed()->whereHas('country', fn ($query) => $query->where('iso2', 'MY'))->select('id'))->exists(),
                default => true,
            };
        } catch (Throwable) {
            return false;
        }
    }

    private function hasPublished(ContentType $type): bool
    {
        return ContentEntry::query()->published($type)->exists();
    }

    private function hasAppStoreLink(): bool
    {
        $homepage = $this->settings->group('homepage');

        return filter_var($homepage['google_play_url'] ?? null, FILTER_VALIDATE_URL) !== false
            || filter_var($homepage['app_store_url'] ?? null, FILTER_VALIDATE_URL) !== false;
    }
}
