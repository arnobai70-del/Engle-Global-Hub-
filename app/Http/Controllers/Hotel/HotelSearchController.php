<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\SearchHotelsRequest;
use App\Services\Hotel\HotelSearchService;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class HotelSearchController extends Controller
{
    public function __invoke(
        SearchHotelsRequest $request,
        TravelServiceRegistry $registry,
        HotelSearchService $searchService,
        TravelDemoCatalog $demo,
        TravelPageContent $content,
    ): View {
        $service = $registry->all()['hotels'];
        $criteria = $request->validated();
        $demoMode = (bool) $service['demo_mode'];

        return view('hotels.results', [
            'criteria' => $criteria,
            'hotels' => $demoMode ? $demo->hotels() : $searchService->search($criteria),
            'demoMode' => $demoMode,
            'pageContent' => $content->for('hotels'),
        ]);
    }
}
