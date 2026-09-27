<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class TourController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content): View
    {
        $service = $registry->all()['tours'];

        return view('tours.index', [
            'service' => $service,
            'demoMode' => $service['demo_mode'],
            'demoTours' => $demo->tours(),
            'pageContent' => $content->for('tours'),
        ]);
    }
}
