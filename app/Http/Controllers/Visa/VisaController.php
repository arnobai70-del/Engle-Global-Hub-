<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ServicePageController;
use App\Models\Country;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VisaController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content, ProviderStatus $providerStatus, Request $request, ServicePageController $pages): View
    {
        // Without a live provider, or for visitors who may not search, show the enquiry page.
        $isLive = $providerStatus->of('visa') === ProviderStatus::LIVE;
        if (! $isLive || $request->user()?->can('visa.view') !== true) {
            return $pages->standalone('visa', $isLive ? route('login') : null);
        }

        $countries = Country::query()
            ->where('is_active', true)
            ->whereNotNull('iso3')
            ->orderBy('name')
            ->get(['name', 'iso3']);
        $service = $registry->all()['visa'];

        return view('visa.index', [
            'service' => $service,
            'demoMode' => $providerStatus->of('visa') !== ProviderStatus::LIVE,
            'countries' => $countries,
            'visaServices' => $demo->visaServices(),
            'visaSteps' => $demo->visaSteps(),
            'visaDocuments' => $demo->visaDocuments(),
            'pageContent' => $content->for('visa'),
        ]);
    }
}
