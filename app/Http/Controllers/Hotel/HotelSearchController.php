<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\SearchHotelsRequest;
use App\Services\Hotel\HotelSearchService;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HotelSearchController extends Controller
{
    public function __invoke(
        SearchHotelsRequest $request,
        TravelServiceRegistry $registry,
        HotelSearchService $searchService,
        TravelDemoCatalog $demo,
        TravelPageContent $content, ProviderStatus $providerStatus,
    ): View|RedirectResponse {
        $service = $registry->all()['hotels'];
        $criteria = $request->validated();
        $demoMode = $providerStatus->of('hotels') !== ProviderStatus::LIVE;

        // Without a connected provider there are no real results: never render sample data as results.
        if ($demoMode) {
            return redirect()->route('hotels.index')->with('api_waiting', __('Online search is waiting for the provider API. Please send an enquiry and our team will help you.'));
        }

        return view('hotels.results', [
            'criteria' => $criteria,
            'hotels' => $demoMode ? $demo->hotels() : $searchService->search($criteria),
            'demoMode' => $demoMode,
            'pageContent' => $content->for('hotels'),
        ]);
    }
}
