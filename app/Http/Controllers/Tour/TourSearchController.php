<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tour\SearchToursRequest;
use App\Services\Integrations\ProviderStatus;
use App\Services\Tour\TourSearchService;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TourSearchController extends Controller
{
    public function __invoke(
        SearchToursRequest $request,
        TravelServiceRegistry $registry,
        TourSearchService $searchService,
        TravelDemoCatalog $demo,
        TravelPageContent $content, ProviderStatus $providerStatus,
    ): View|RedirectResponse {
        $service = $registry->all()['tours'];
        $criteria = $request->validated();
        $demoMode = $providerStatus->of('tours') !== ProviderStatus::LIVE;

        // Without a connected provider there are no real results: never render sample data as results.
        if ($demoMode) {
            return redirect()->route('tours.index')->with('api_waiting', __('Online search is waiting for the provider API. Please send an enquiry and our team will help you.'));
        }

        return view('tours.results', [
            'criteria' => $criteria,
            'tours' => $demoMode ? $demo->tours() : $searchService->search($criteria),
            'demoMode' => $demoMode,
            'pageContent' => $content->for('tours'),
        ]);
    }
}
