<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class TourController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content, ProviderStatus $providerStatus): View
    {
        $service = $registry->all()['tours'];

        return view('tours.index', [
            'service' => $service,
            'demoMode' => $providerStatus->of('tours') !== ProviderStatus::LIVE,
            'demoTours' => $demo->tours(),
            'pageContent' => $content->for('tours'),
        ]);
    }
}
