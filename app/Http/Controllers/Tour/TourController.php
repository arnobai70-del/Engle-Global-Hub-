<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ServicePageController;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content, ProviderStatus $providerStatus, Request $request, ServicePageController $pages): View
    {
        // Without a live provider, or for visitors who may not search, show the enquiry page.
        $isLive = $providerStatus->of('tours') === ProviderStatus::LIVE;
        if (! $isLive || $request->user()?->can('tours.search') !== true) {
            return $pages->standalone('tours', $isLive ? route('login') : null);
        }

        $service = $registry->all()['tours'];

        return view('tours.index', [
            'service' => $service,
            'demoMode' => $providerStatus->of('tours') !== ProviderStatus::LIVE,
            'demoTours' => $demo->tours(),
            'pageContent' => $content->for('tours'),
        ]);
    }
}
