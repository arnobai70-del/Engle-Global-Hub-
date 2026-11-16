<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visa\CheckVisaRequirementsRequest;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use App\Services\Visa\VisaRequirementService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VisaRequirementController extends Controller
{
    public function __invoke(
        CheckVisaRequirementsRequest $request,
        TravelServiceRegistry $registry,
        VisaRequirementService $requirementService,
        TravelDemoCatalog $demo,
        TravelPageContent $content, ProviderStatus $providerStatus,
    ): View|RedirectResponse {
        $service = $registry->all()['visa'];
        $criteria = $request->validated();
        $demoMode = $providerStatus->of('visa') !== ProviderStatus::LIVE;

        // Without a connected provider there are no real results: never render sample data as results.
        if ($demoMode) {
            return redirect()->route('visa.index')->with('api_waiting', __('Online search is waiting for the provider API. Please send an enquiry and our team will help you.'));
        }

        return view('visa.requirements', [
            'criteria' => $criteria,
            'information' => $demoMode
                ? $demo->visaRequirementPreview($criteria)
                : $requirementService->requirements($criteria),
            'demoMode' => $demoMode,
            'pageContent' => $content->for('visa'),
        ]);
    }
}
