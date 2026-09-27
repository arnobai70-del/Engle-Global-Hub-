<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tour\SearchToursRequest;
use App\Services\Tour\TourSearchService;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class TourSearchController extends Controller
{
    public function __invoke(
        SearchToursRequest $request,
        TravelServiceRegistry $registry,
        TourSearchService $searchService,
        TravelDemoCatalog $demo,
        TravelPageContent $content,
    ): View {
        $service = $registry->all()['tours'];
        $criteria = $request->validated();
        $demoMode = (bool) $service['demo_mode'];

        return view('tours.results', [
            'criteria' => $criteria,
            'tours' => $demoMode ? $demo->tours() : $searchService->search($criteria),
            'demoMode' => $demoMode,
            'pageContent' => $content->for('tours'),
        ]);
    }
}
