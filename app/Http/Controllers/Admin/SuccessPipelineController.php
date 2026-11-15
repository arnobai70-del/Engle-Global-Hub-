<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\StudentApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * "Upcoming Success": an internal view of applications close to completion.
 * It never predicts outcomes; it lists the next possible milestone only.
 */
class SuccessPipelineController extends Controller
{
    /** Statuses after an offer is received, until the file closes. */
    public const PIPELINE_STATUSES = [
        StudentApplicationStatus::OfferReceived,
        StudentApplicationStatus::StudentsPassPreparation,
        StudentApplicationStatus::ApplicationSubmitted,
        StudentApplicationStatus::IpaIssued,
        StudentApplicationStatus::FormalitiesPending,
        StudentApplicationStatus::FurtherActionRequired,
    ];

    public function index(Request $request): View
    {
        $user = $request->user();

        $applications = StudentApplication::query()
            ->with(['student:id,first_name,last_name', 'destinationCountry:id,name', 'assignedConsultant:id,name'])
            ->whereIn('status', array_map(fn (StudentApplicationStatus $status): string => $status->value, self::PIPELINE_STATUSES))
            ->when(! $user->can('student-applications.view-all'), fn ($query) => $query->where('assigned_consultant_id', $user->getKey()))
            ->orderByRaw('follow_up_at is null')->orderBy('follow_up_at')->orderBy('updated_at')
            ->paginate(30);

        return view('admin.success-pipeline.index', ['applications' => $applications]);
    }

    public function followUp(Request $request, StudentApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        $data = $request->validate(['follow_up_at' => ['nullable', 'date_format:Y-m-d']]);
        $application->forceFill(['follow_up_at' => $data['follow_up_at'] ?? null])->save();

        return back()->with('status', 'Follow-up date saved for '.$application->reference.'.');
    }
}
