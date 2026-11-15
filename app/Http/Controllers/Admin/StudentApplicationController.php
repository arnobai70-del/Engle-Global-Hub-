<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentApplicationStatus;
use App\Enums\StudentDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentApplicationRequest;
use App\Http\Requests\Admin\TransitionStudentApplicationRequest;
use App\Models\Country;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StudentApplicationController extends Controller
{
    public function __construct(private StudentApplicationWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = StudentApplicationStatus::tryFrom((string) $request->query('status', ''));

        $applications = StudentApplication::query()
            ->with(['student:id,first_name,last_name,email', 'destinationCountry:id,name', 'institution:id,name', 'assignedConsultant:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('course_name', 'like', '%'.$search.'%')
                        ->orWhereHas('student', function ($query) use ($search): void {
                            $query->where('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->when(! $request->user()->can('student-applications.view-all'), fn ($query) => $query->where('assigned_consultant_id', $request->user()->getKey()))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.student-applications.index', [
            'applications' => $applications,
            'filters' => ['search' => $search, 'status' => $status?->value ?? ''],
            'statuses' => StudentApplicationStatus::cases(),
        ]);
    }

    public function create(Student $student): View
    {
        return view('admin.student-applications.create', [
            'student' => $student,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreStudentApplicationRequest $request, Student $student): RedirectResponse
    {
        $application = $this->workflow->open($student, $this->withSelfAssignment($request->applicationAttributes(), $request->user()), $request->user());

        return redirect()
            ->route('admin.student-applications.show', $application)
            ->with('status', 'Student application '.$application->reference.' opened.');
    }

    public function show(StudentApplication $application): View
    {
        Gate::authorize('view', $application);

        $application->load([
            'student', 'destinationCountry:id,name', 'institution:id,name',
            'assignedConsultant:id,name', 'enquiry:id,reference', 'statusChanges.changedBy:id,name',
            'documents.uploadedBy:id,name', 'documents.reviewedBy:id,name', 'messages.author:id,name',
            'appointments.staff:id,name', 'payments',
        ]);

        return view('admin.student-applications.show', [
            'application' => $application,
            'nextStatuses' => $application->status->allowedTransitions(),
            'documentTypes' => StudentDocumentType::cases(),
            ...$this->formOptions(),
        ]);
    }

    public function update(StoreStudentApplicationRequest $request, StudentApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        $data = $request->applicationAttributes();
        $data['internal_notes'] = $request->validate(['internal_notes' => ['nullable', 'string', 'max:5000']])['internal_notes'] ?? null;
        $consultantId = $data['assigned_consultant_id'] ?? null;
        unset($data['assigned_consultant_id']);

        $application->forceFill($data)->save();
        $this->workflow->assignConsultant(
            $application,
            $consultantId === null ? null : User::query()->findOrFail($consultantId),
            $request->user(),
        );

        return redirect()->route('admin.student-applications.show', $application)->with('status', 'Application details updated.');
    }

    public function transition(TransitionStudentApplicationRequest $request, StudentApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $this->workflow->transition(
            $application,
            $request->targetStatus(),
            $request->user(),
            $request->validated('student_note'),
            $request->validated('internal_note'),
        );

        return redirect()
            ->route('admin.student-applications.show', $application)
            ->with('status', 'Status changed to '.$application->status->label().'.');
    }

    /**
     * Staff who cannot see every application are assigned to the ones they
     * open, so they keep access to their own work.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function withSelfAssignment(array $attributes, User $user): array
    {
        if (empty($attributes['assigned_consultant_id']) && ! $user->can('student-applications.view-all')) {
            $attributes['assigned_consultant_id'] = $user->getKey();
        }

        return $attributes;
    }

    /**
     * @return array{countries: Collection<int, Country>, institutions: Collection<int, Institution>, staff: Collection<int, User>, singaporeId: int|null}
     */
    private function formOptions(): array
    {
        $countries = Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'iso2']);

        return [
            'countries' => $countries,
            'institutions' => Institution::query()->where('status', Institution::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']),
            'courses' => Course::query()->where('is_active', true)->with(['institution:id,name', 'intakes' => fn ($query) => $query->where('is_open', true)])->orderBy('name')->get(),
            'staff' => User::query()->whereKey(StoreStudentApplicationRequest::staffUserIds())->orderBy('name')->get(['id', 'name']),
            'singaporeId' => $countries->firstWhere('iso2', 'SG')?->id,
        ];
    }
}
