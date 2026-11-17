<?php

namespace App\Services\Navigation;

use App\Models\ServiceEnquiry;
use App\Services\Travel\ServiceCatalog;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * The destinations a menu item may point to. Internal targets come from a vetted
 * list of public pages, so an admin can never create a link to a missing route;
 * external targets must be https URLs.
 *
 * Stored format: "route:{name}", "route:{name}|{parameter}" or "url:{https url}".
 */
class MenuTargets
{
    /** Public pages without required parameters, with the feature switch that hides them. */
    private const ROUTES = [
        'home' => ['Home', null],
        'consultancy.malaysia' => ['Study in Malaysia', 'student-consultancy'],
        'consultancy.malaysia.guides.index' => ['Malaysia Guides', 'student-consultancy'],
        'consultancy.malaysia.institutions.index' => ['Institutions in Malaysia', 'student-consultancy'],
        'consultancy.singapore' => ['Study in Singapore', 'student-consultancy'],
        'consultancy.guides.index' => ['Singapore Guides', 'student-consultancy'],
        'consultancy.institutions.index' => ['Institutions', 'student-consultancy'],
        'success-stories.index' => ['Success Stories', null],
        'travel-services.index' => ['Travel Services', null],
        'visa.index' => ['Visa information', 'visa'],
        'work-visa.index' => ['Work Visa', 'visa'],
        'hotels.index' => ['Hotels', 'hotels'],
        'tours.index' => ['Tours', 'tours'],
        'offers.index' => ['Offers & Promotions', 'about'],
        'rewards.index' => ['Rewards', null],
        'explore-bangladesh.index' => ['Explore Bangladesh', null],
        'inspiration.index' => ['Get Inspired', null],
        'restaurants.index' => ['Restaurants', null],
        'reviews.index' => ['Reviews', null],
        'forums.index' => ['Forums', null],
        'map' => ['Map', null],
        'about' => ['About Us', 'about'],
        'why-us' => ['Why Us', null],
        'privacy' => ['Privacy Policy', null],
        'business-services.index' => ['Business Services', null],
        'b2b.register' => ['Egabe B2B (partner application)', null],
        'b2b.info' => ['B2B partners (information)', null],
        'support' => ['Contact & Support', 'support'],
        'terms' => ['Terms & Conditions', null],
        'search' => ['Search', null],
        'mobile-app' => ['Mobile App', null],
        'login' => ['Login', null],
        'register' => ['Register', null],
        'dashboard' => ['Dashboard (signed in)', null],
        'student.applications.index' => ['My Applications (signed in)', 'student-consultancy'],
        'service-enquiries.mine' => ['My Enquiries (signed in)', null],
        'notifications.index' => ['Notifications (signed in)', null],
        'account.overview' => ['My Account (signed in)', 'account'],
        'agent.index' => ['Agent Portal (agents)', null],
    ];

    public function __construct(private ServiceCatalog $catalog) {}

    /**
     * Grouped options for the admin form: [group => [target => label]].
     *
     * @return array<string, array<string, string>>
     */
    public function options(): array
    {
        $pages = [];
        foreach (self::ROUTES as $name => [$label]) {
            if (Route::has($name)) {
                $pages['route:'.$name] = $label;
            }
        }

        $services = [];
        foreach ($this->catalog->all() as $slug => $service) {
            $services['route:services.show|'.$slug] = $service['title'];
        }

        $enquiries = [];
        foreach (ServiceEnquiry::SERVICES as $service) {
            $slug = str_replace('_', '-', $service);
            $enquiries['route:service-enquiries.create|'.$slug] = 'Enquiry form: '.ucwords(str_replace('_', ' ', $service));
        }

        return ['Pages' => $pages, 'Service pages' => $services, 'Enquiry forms' => $enquiries];
    }

    public function isAllowed(?string $target): bool
    {
        if ($target === null) {
            return false;
        }
        if (str_starts_with($target, 'url:')) {
            return $this->isSafeExternal(substr($target, 4));
        }

        return collect($this->options())->flatMap(fn (array $group): array => $group)->has($target);
    }

    /**
     * The URL for a stored target, or null when it cannot be resolved (never a broken link).
     */
    public function url(?string $target, ?string $fragment = null): ?string
    {
        if ($target === null) {
            return null;
        }

        if (str_starts_with($target, 'url:')) {
            $url = substr($target, 4);

            return $this->isSafeExternal($url) ? $url : null;
        }

        if (! str_starts_with($target, 'route:')) {
            return null;
        }

        [$name, $parameter] = array_pad(explode('|', substr($target, 6), 2), 2, null);
        if (! Route::has($name)) {
            return null;
        }
        if ($name === 'services.show' && ($parameter === null || $this->catalog->find($parameter) === null)) {
            return null;
        }

        try {
            $url = $parameter === null ? route($name) : route($name, $parameter);
        } catch (Throwable) {
            return null;
        }

        return $url.(filled($fragment) ? '#'.$fragment : '');
    }

    /**
     * The route name of an internal target, or null for external links and headings.
     */
    public function routeName(?string $target): ?string
    {
        return $target !== null && str_starts_with($target, 'route:') ? explode('|', substr($target, 6), 2)[0] : null;
    }

    /**
     * The feature switch that controls a target's page, if any.
     */
    public function featureFor(?string $target): ?string
    {
        if ($target === null || ! str_starts_with($target, 'route:')) {
            return null;
        }
        $name = explode('|', substr($target, 6), 2)[0];

        return self::ROUTES[$name][1] ?? null;
    }

    public function isSafeExternal(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && str_starts_with(strtolower($url), 'https://')
            && ! str_contains($url, ' ')
            && strlen($url) <= 290;
    }
}
