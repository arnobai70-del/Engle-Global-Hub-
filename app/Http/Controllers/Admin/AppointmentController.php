<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentApplicationRequest;
use App\Models\Appointment;
use App\Models\StudentApplication;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Services\Consultancy\AppointmentScheduler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentScheduler $scheduler) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = array_key_exists((string) $request->query('status'), Appointment::STATUSES) ? (string) $request->query('status') : '';

        return view('admin.appointments.index', [
            'appointments' => Appointment::query()
                ->with(['enquiry:id,reference,full_name', 'application.student:id,first_name,last_name', 'staff:id,name'])
                ->when(! $user->can('student-applications.view-all'), fn ($query) => $query->where(fn ($query) => $query
                    ->where('staff_user_id', $user->getKey())->orWhereNull('staff_user_id')))
                ->when($status !== '', fn ($query) => $query->where('status', $status), fn ($query) => $query->where('scheduled_at', '>=', now()->startOfDay()))
                ->orderBy('scheduled_at')->paginate(25)->withQueryString(),
            'status' => $status,
            'staff' => $this->staff(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);
        if (! empty($data['student_application_id'])) {
            Gate::authorize('update', StudentApplication::query()->findOrFail($data['student_application_id']));
        }

        $this->scheduler->create($data, $data['status'], $request->user());

        return back()->with('status', 'Appointment saved.');
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($appointment->application !== null) {
            Gate::authorize('update', $appointment->application);
        }

        $this->scheduler->update($appointment, $this->validated($request, $appointment), $request->user());

        return back()->with('status', 'Appointment updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Appointment $appointment): array
    {
        return $request->validate([
            'student_enquiry_id' => [$appointment ? 'prohibited' : 'nullable', 'integer', Rule::exists(StudentEnquiry::class, 'id')],
            'student_application_id' => [$appointment ? 'prohibited' : 'required_without:student_enquiry_id', 'nullable', 'integer', Rule::exists(StudentApplication::class, 'id')],
            'staff_user_id' => ['nullable', 'integer', Rule::in(StoreStudentApplicationRequest::staffUserIds())],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'between:15,240'],
            'mode' => ['required', Rule::in(array_keys(Appointment::MODES))],
            'location_or_link' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /**
     * @return Collection<int, User>
     */
    private function staff()
    {
        return User::query()->whereKey(StoreStudentApplicationRequest::staffUserIds())->orderBy('name')->get(['id', 'name']);
    }
}
