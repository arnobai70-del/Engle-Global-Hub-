<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class HotelController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content): View
    {
        $service = $registry->all()['hotels'];

        return view('hotels.index', [
            'service' => $service,
            'demoMode' => $service['demo_mode'],
            'demoHotels' => $demo->hotels(),
            'pageContent' => $content->for('hotels'),
        ]);
    }
}
