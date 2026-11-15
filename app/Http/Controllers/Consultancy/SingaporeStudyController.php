<?php

namespace App\Http\Controllers\Consultancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultancy\StoreStudentEnquiryRequest;
use App\Models\StudentEnquiry;
use Illuminate\View\View;

class SingaporeStudyController extends Controller
{
    public function __invoke(): View
    {
        $destinations = StoreStudentEnquiryRequest::destinations();

        return view('consultancy.singapore', [
            'destinations' => $destinations,
            'studyLevels' => StudentEnquiry::STUDY_LEVELS,
            'leadSources' => StudentEnquiry::LEAD_SOURCES,
        ]);
    }
}
