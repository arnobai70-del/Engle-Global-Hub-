<?php

namespace App\Http\Controllers\Visa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visa\CheckVisaRequirementsRequest;
use App\Services\Travel\TravelDemoCatalog;
use App\Services\Travel\TravelPageContent;
use App\Services\Travel\TravelServiceRegistry;
use App\Services\Visa\VisaRequirementService;
use Illuminate\Contracts\View\View;

class VisaRequirementController extends Controller
{
    public function __invoke(
        CheckVisaRequirementsRequest $request,
        TravelServiceRegistry $registry,
        VisaRequirementService $requirementService,
        TravelDemoCatalog $demo,
        TravelPageContent $content,
    ): View {
        $service = $registry->all()['visa'];
        $criteria = $request->validated();
        $demoMode = (bool) $service['demo_mode'];

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
