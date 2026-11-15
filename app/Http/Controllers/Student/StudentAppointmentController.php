<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StudentApplication;
use App\Services\Consultancy\AppointmentScheduler;
use App\Services\Consultancy\StudentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentAppointmentController extends Controller
{
    /**
     * A student requests a consultation slot; staff confirm or propose another time.
     */
    public function store(Request $request, StudentApplication $application, StudentPortal $portal, AppointmentScheduler $scheduler): RedirectResponse
    {
        $portal->authorize($request->user(), $application);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:+1 hour', 'before:+90 days'],
            'mode' => ['required', Rule::in(array_keys(Appointment::MODES))],
            'student_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($application->appointments()->where('status', Appointment::STATUS_REQUESTED)->count() >= 2) {
            return back()->withErrors(['scheduled_at' => 'You already have pending appointment requests. Your consultant will reply soon.']);
        }

        $scheduler->create([
            ...$data,
            'student_application_id' => $application->getKey(),
            'staff_user_id' => $application->assigned_consultant_id,
            'duration_minutes' => 30,
        ], Appointment::STATUS_REQUESTED, $request->user());

        return redirect()->route('student.applications.show', $application)->with('status', 'Appointment requested. Your consultant will confirm the time.');
    }
}
