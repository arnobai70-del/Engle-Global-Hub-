<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Institution;
use App\Services\Consultancy\StudyCountries;
use Illuminate\View\View;

/**
 * Public list of institutions the owner has verified for listing. Only safe
 * fields are shown (name, country, website, active courses with open intakes);
 * contact details, registration numbers and internal notes stay private.
 */
class InstitutionDirectoryController extends Controller
{
    public function __invoke(StudyCountries $countries, string $country = 'SG'): View
    {
        $study = $countries->get($country);
        $institutions = Institution::query()->publiclyListed()
            // Malaysia lists Malaysian institutions; Singapore keeps every other listed institution.
            ->when($study['code'] === 'MY',
                fn ($query) => $query->whereHas('country', fn ($country) => $country->where('iso2', 'MY')),
                fn ($query) => $query->where(fn ($inner) => $inner->whereNull('country_id')->orWhereHas('country', fn ($country) => $country->where('iso2', '!=', 'MY'))))
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
