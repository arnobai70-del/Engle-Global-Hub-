<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Institution;
use App\Services\Consultancy\StudyCountries;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public list of institutions the owner has verified for listing. Only safe
 * fields are shown (name, country, website, active courses with open intakes);
 * contact details, registration numbers and internal notes stay private.
 */
class InstitutionDirectoryController extends Controller
{
    public function __invoke(Request $request, StudyCountries $countries): View
    {
        $study = $countries->forRoute($request);
        // Each destination lists its own country's institutions; Singapore keeps every other listed institution.
        $institutions = $countries->scopeInstitutions(Institution::query()->publiclyListed(), $study['code'])
            ->with([
                'country:id,name',
                'courses' => fn ($query) => $query->where('is_active', true)
                    ->with(['intakes' => fn ($intakes) => $intakes->where('is_open', true)->whereDate('starts_on', '>=', today())->orderBy('starts_on')]),
            ])
            ->get(['id', 'name', 'website_url', 'country_id']);

        $admissionGuides = ContentEntry::query()->published(ContentType::Guide)
            ->whereIn('slug', ['college-admission', 'university-admission', 'course-selection'])
            ->where(fn ($query) => $query->whereNull('review_status')->orWhere('review_status', ContentEntry::REVIEW_APPROVED))
            ->get();

        $admissionGuides = $admissionGuides->filter(fn (ContentEntry $guide): bool => $countries->forGuide($guide)['code'] === $study['code'])->values();

        return view('consultancy.institutions', compact('institutions', 'admissionGuides', 'study'));
    }
}
