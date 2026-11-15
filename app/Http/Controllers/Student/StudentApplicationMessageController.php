<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentApplication;
use App\Models\StudentApplicationMessage;
use App\Notifications\StudentApplicationMessageReceived;
use App\Services\ActivityRecorder;
use App\Services\Consultancy\StudentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentApplicationMessageController extends Controller
{
    public function store(Request $request, StudentApplication $application, StudentPortal $portal, ActivityRecorder $activity): RedirectResponse
    {
        $portal->authorize($request->user(), $application);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);

        $message = $application->messages()->create([
            'author_user_id' => $request->user()->getKey(),
            'author_type' => StudentApplicationMessage::AUTHOR_STUDENT,
            'body' => $data['body'],
        ]);
        $activity->record($request->user(), 'student_application.message_sent', $application, ['reference' => $application->reference]);

        $application->assignedConsultant?->notify(new StudentApplicationMessageReceived($message));

        return redirect()->route('student.applications.show', $application)->with('status', 'Message sent to your consultant.');
    }
}
