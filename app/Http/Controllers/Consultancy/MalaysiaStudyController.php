<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Course;
use App\Models\Institution;
use App\Services\Consultancy\StudyCountries;
use App\Services\SiteFacts;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

/**
 * Study in Malaysia — the main study destination. The Student Pass steps on this
 * page follow official EMGS information (educationmalaysia.gov.my); costs in MYR
 * come only from Admin › Business facts, and courses only from Malaysian
 * institutions the owner has approved for public listing.
 */
class MalaysiaStudyController extends Controller
{
    public function __invoke(SiteFacts $facts, StudyCountries $countries): View
    {
        try {
            $courses = Course::query()->where('is_active', true)
                ->whereIn('institution_id', Institution::query()->publiclyListed()
                    ->whereHas('country', fn ($query) => $query->where('iso2', 'MY'))->select('id'))
                ->with(['institution:id,name', 'intakes' => fn ($query) => $query->where('is_open', true)->where('starts_on', '>=', now()->toDateString())->orderBy('starts_on')])
                ->orderBy('name')->get();
            $guides = $countries->scopeGuides(ContentEntry::query()->published(ContentType::Guide), 'MY')->limit(4)->get();
        } catch (QueryException) {
            $courses = collect();
            $guides = collect();
        }

        return view('consultancy.malaysia', [
            'courses' => $courses,
            'guides' => $guides,
            'intakes' => $facts->get('my_intakes'),
            'living' => $facts->rows([
                'my_living_room' => __('Shared room'),
                'my_living_food' => __('Food'),
                'my_living_transport' => __('Public transport'),
                'my_living_phone' => __('Phone & personal'),
            ], 'MYR '),
        ]);
    }
}
