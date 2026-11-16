<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class HotelController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content, ProviderStatus $providerStatus): View
    {
        $service = $registry->all()['hotels'];

        return view('hotels.index', [
            'service' => $service,
            'demoMode' => $providerStatus->of('hotels') !== ProviderStatus::LIVE,
            'demoHotels' => $demo->hotels(),
            'pageContent' => $content->for('hotels'),
        ]);
    }
}
