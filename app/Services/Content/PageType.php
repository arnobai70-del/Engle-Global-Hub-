<?php

namespace App\Services\Content;

use App\Services\Travel\ServiceCatalog;
use Illuminate\Http\Request;

/**
 * The kind of public page, set per route in one place, so the layout shows the right
 * footer call-to-action band:
 * - study:   study destinations, guides, institutions, the student-visa service, planning tools;
 * - travel:  travel and visa services (/travel-services, /tours, /hotels, /flights, /visa, travel /services/*);
 * - explore: Explore Bangladesh (travel band with "Plan my trip");
 * - neutral: About, Why Us, Contact, legal, search, B2B and everything else;
 * - none:    pages that already end with their own consultation form (home, Study in Malaysia / Singapore).
 * A view can still override it with @section('page_type', '...').
 */
class PageType
{
    public const STUDY = 'study';

    public const TRAVEL = 'travel';

    public const EXPLORE = 'explore';

    public const NEUTRAL = 'neutral';

    public const NONE = 'none';

    public const ALL = [self::STUDY, self::TRAVEL, self::EXPLORE, self::NEUTRAL, self::NONE];

    /** Route name patterns per type, checked in this order. */
    private const ROUTES = [
        self::NONE => ['home', 'consultancy.malaysia', 'consultancy.singapore'],
        self::EXPLORE => ['explore-bangladesh.*'],
        self::STUDY => ['consultancy.*', 'appointments.book', 'student.*', 'success-stories.*'],
        self::TRAVEL => ['travel-services.*', 'tours.*', 'hotels.*', 'flights.*', 'visa.*', 'work-visa.*', 'service-enquiries.create', 'offers.*'],
    ];

    /** Service catalogue groups => type of their /services/{slug} page. */
    private const SERVICE_GROUPS = ['study' => self::STUDY, 'visa' => self::TRAVEL, 'travel' => self::TRAVEL, 'transport' => self::TRAVEL];

    public function __construct(private ServiceCatalog $catalog) {}

    public function for(Request $request): string
    {
        $route = $request->route();
        if ($route === null) {
            return self::NEUTRAL;
        }

        if ($request->routeIs('services.show')) {
            $slug = (string) $route->parameter('slug');
            // Foreigner support is travel help for visitors; other business services stay neutral.
            if ($slug === 'foreigner-support') {
                return self::TRAVEL;
            }
            $service = $this->catalog->find($slug);

            return self::SERVICE_GROUPS[$service['group'] ?? ''] ?? self::NEUTRAL;
        }

        foreach (self::ROUTES as $type => $patterns) {
            if ($request->routeIs(...$patterns)) {
                return $type;
            }
        }

        return self::NEUTRAL;
    }
}
