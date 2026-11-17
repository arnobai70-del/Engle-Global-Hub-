<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ServicePageController;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content, ProviderStatus $providerStatus, Request $request, ServicePageController $pages): View
    {
        // Without a live provider, or for visitors who may not search, show the enquiry page.
        $isLive = $providerStatus->of('hotels') === ProviderStatus::LIVE;
        if (! $isLive || $request->user()?->can('hotels.search') !== true) {
            return $pages->standalone('hotels', $isLive ? route('login') : null);
        }

        $service = $registry->all()['hotels'];

        return view('hotels.index', [
            'service' => $service,
            'demoMode' => $providerStatus->of('hotels') !== ProviderStatus::LIVE,
            'demoHotels' => $demo->hotels(),
            'pageContent' => $content->for('hotels'),
        ]);
    }
}
