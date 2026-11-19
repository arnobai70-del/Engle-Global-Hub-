<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Models\Institution;
use App\Models\StudyDestination;
use App\Models\StudyDestinationItem;
use App\Services\Consultancy\MalaysiaCostCalculator;
use App\Services\Consultancy\StudyCountries;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /study-in-{slug}: one data-driven page per study destination (Admin › Study destinations).
 * Sections without content are not shown; the page itself returns 404 until it is published
 * and complete, so menus keep pointing to the Student Visa enquiry form until then.
 */
class StudyDestinationController extends Controller
{
    public function __invoke(Request $request, StudyCountries $countries, MalaysiaCostCalculator $amounts): View
    {
        $slug = (string) $request->route('destination');
        $destination = $countries->publicDestinations()->first(fn (StudyDestination $destination): bool => $destination->slug === $slug);
        abort_if($destination === null, 404);
        $destination->loadMissing('heroImage');
        $study = $countries->forDestination($destination);

        $rate = $destination->bdt_rate !== null && (float) $destination->bdt_rate > 0 ? (float) $destination->bdt_rate : null;
        $costs = $destination->section('cost')->filter(fn (StudyDestinationItem $item): bool => filled($item->title))
            ->map(function (StudyDestinationItem $item) use ($amounts, $rate, $destination): array {
                // An amount written as a number or range ("8,000–12,000") also gets a Taka estimate.
                $range = $rate !== null ? $amounts->parseAmount($item->amount) : null;

                return [
                    'item' => $item,
                    'amount' => filled($item->amount) ? trim(($destination->currency_code ? $destination->currency_code.' ' : '').(string) $item->amount) : null,
                    'bdt' => $range === null ? null : ($range[0] === $range[1]
                        ? 'BDT '.number_format($range[0] * $rate)
                        : 'BDT '.number_format($range[0] * $rate).'–'.number_format($range[1] * $rate)),
                ];
            })->values();

        $institutionCount = $countries->scopeInstitutions(Institution::query()->publiclyListed(), $study['code'])->count();
        $guides = $countries->scopeGuides(ContentEntry::query()->published(ContentType::Guide), $study['code'])
            ->where(fn ($query) => $query->whereNull('review_status')->orWhere('review_status', ContentEntry::REVIEW_APPROVED))
            ->limit(4)->get();

        return view('consultancy.destination', [
            'destination' => $destination,
            'study' => $study,
            'costs' => $costs,
            'rate' => $rate,
            'institutionCount' => $institutionCount,
            'guides' => $guides,
        ]);
    }
}
