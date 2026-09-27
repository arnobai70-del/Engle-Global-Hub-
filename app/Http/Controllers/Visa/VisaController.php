<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use Illuminate\Contracts\View\View;

class VisaController extends Controller
{
    public function __invoke(TravelServiceRegistry $registry, TravelDemoCatalog $demo, TravelPageContent $content): View
    {
        $countries = Country::query()
            ->where('is_active', true)
            ->whereNotNull('iso3')
            ->orderBy('name')
            ->get(['name', 'iso3']);
        $service = $registry->all()['visa'];

        return view('visa.index', [
            'service' => $service,
            'demoMode' => $service['demo_mode'],
            'countries' => $countries,
            'visaServices' => $demo->visaServices(),
            'visaSteps' => $demo->visaSteps(),
            'visaDocuments' => $demo->visaDocuments(),
            'pageContent' => $content->for('visa'),
        ]);
    }
}
