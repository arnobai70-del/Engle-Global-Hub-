<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\BusinessService;
use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Course;
use App\Models\Institution;
use App\Models\ServiceDestination;
use App\Services\SiteFacts;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

/**
 * Study in Singapore (DESIGN_BRIEF.md): course finder, requirements, documents,
 * Student's Pass timeline, living costs and the free consultation form. Courses are
 * listed only for institutions the owner has approved for public listing.
 */
class SingaporeStudyController extends Controller
{
    public function __invoke(SiteFacts $facts): View
    {
        try {
            $courses = Course::query()->where('is_active', true)
                ->whereIn('institution_id', Institution::query()->publiclyListed()->select('id'))
                ->with(['institution:id,name', 'intakes' => fn ($query) => $query->where('is_open', true)->where('starts_on', '>=', now()->toDateString())->orderBy('starts_on')])
                ->orderBy('name')->get();
            $guides = ContentEntry::query()->published(ContentType::Guide)->limit(4)->get();
            // Other study destinations staff manage in Admin › Services & Destinations (Singapore first).
            $destinations = ServiceDestination::query()->where('service', BusinessService::StudentVisa->value)->where('is_active', true)
                ->with('country:id,name,iso2')->orderBy('sort_order')->get()->pluck('country')->filter()->values();
        } catch (QueryException) {
            $courses = collect();
            $guides = collect();
            $destinations = collect();
        }

        return view('consultancy.singapore', [
            'courses' => $courses,
            'guides' => $guides,
            'destinations' => $destinations,
            'intakes' => $facts->get('intakes'),
            'living' => $facts->rows([
                'living_room' => __('Shared room'),
                'living_food' => __('Food'),
                'living_transport' => __('Public transport'),
                'living_phone' => __('Phone & personal'),
            ]),
        ]);
    }
}
