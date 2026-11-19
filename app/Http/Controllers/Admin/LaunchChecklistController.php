<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\LaunchChecklist;
use Illuminate\View\View;

class LaunchChecklistController extends Controller
{
    /**
     * Admin › Website launch checklist.
     */
    public function __invoke(LaunchChecklist $checklist): View
    {
        $sections = $checklist->sections();

        return view('admin.launch-checklist', [
            'sections' => $sections,
            'counts' => $checklist->counts($sections),
        ]);
    }
}
