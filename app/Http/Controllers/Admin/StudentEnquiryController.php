<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentApplicationRequest;
use App\Models\Country;
use App\Models\Course;
use App\Models\Institution;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Services\Consultancy\StudentEnquiryConverter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        if (! in_array($status, StudentEnquiry::STATUSES, true)) {
            $status = '';
        }

        $enquiries = StudentEnquiry::query()
            ->with(['destinationCountry:id,name', 'assignedTo:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('reference', 'like', '%'.$search.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.student-enquiries.index', [
            'enquiries' => $enquiries,
            'filters' => ['search' => $search, 'status' => $status],
            'statuses' => StudentEnquiry::STATUSES,
        ]);
    }

    public function show(StudentEnquiry $enquiry): View
    {
        $enquiry->load(['destinationCountry:id,name', 'assignedTo:id,name', 'student', 'applications']);

        return view('admin.student-enquiries.show', [
            'enquiry' => $enquiry,
            'statuses' => array_values(array_diff(StudentEnquiry::STATUSES, [StudentEnquiry::STATUS_CONVERTED])),
            'staff' => $this->staff(),
            'countries' => Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'institutions' => Institution::query()->where('status', Institution::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
            'courses' => Course::query()->where('is_active', true)->with(['institution:id,name', 'intakes' => fn ($query) => $query->where('is_open', true)])->orderBy('name')->get(),
            'studyLevels' => StudentEnquiry::STUDY_LEVELS,
            'leadSources' => StudentEnquiry::LEAD_SOURCES,
        ]);
    }

    public function update(Request $request, StudentEnquiry $enquiry): RedirectResponse
    {
        $editableStatuses = array_values(array_diff(StudentEnquiry::STATUSES, [StudentEnquiry::STATUS_CONVERTED]));

        $data = $request->validate([
            'status' => [
                Rule::requiredIf($enquiry->status !== StudentEnquiry::STATUS_CONVERTED),
                'nullable', 'string', Rule::in($editableStatuses),
            ],
            'assigned_to_user_id' => ['nullable', 'integer', Rule::in(StoreStudentApplicationRequest::staffUserIds())],
            'follow_up_at' => ['nullable', 'date'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        // A converted enquiry keeps its converted status; only CRM fields stay editable.
        if ($enquiry->status === StudentEnquiry::STATUS_CONVERTED) {
            unset($data['status']);
        }

        $enquiry->forceFill($data)->save();

        return redirect()->route('admin.student-enquiries.show', $enquiry)->with('status', 'Enquiry updated.');
    }

    public function convert(
        StoreStudentApplicationRequest $request,
        StudentEnquiry $enquiry,
        StudentEnquiryConverter $converter,
    ): RedirectResponse {
        $application = $converter->convert($enquiry, StudentApplicationController::withSelfAssignment($request->applicationAttributes(), $request->user()), $request->user());

        return redirect()
            ->route('admin.student-applications.show', $application)
            ->with('status', 'Enquiry converted. Student application '.$application->reference.' opened.');
    }

    /**
     * @return Collection<int, User>
     */
    private function staff(): Collection
    {
        return User::query()->whereKey(StoreStudentApplicationRequest::staffUserIds())->orderBy('name')->get(['id', 'name']);
    }
}
