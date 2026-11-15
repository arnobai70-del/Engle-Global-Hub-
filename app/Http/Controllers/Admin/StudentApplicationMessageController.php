<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentApplication;
use App\Models\StudentApplicationMessage;
use App\Notifications\StudentApplicationMessageReceived;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentApplicationMessageController extends Controller
{
    public function store(Request $request, StudentApplication $application, ActivityRecorder $activity): RedirectResponse
    {
        Gate::authorize('update', $application);
        $data = $request->validate(['body' => ['required', 'string', 'max:3000']]);

        $message = $application->messages()->create([
            'author_user_id' => $request->user()->getKey(),
            'author_type' => StudentApplicationMessage::AUTHOR_STAFF,
            'body' => $data['body'],
        ]);
        $activity->record($request->user(), 'student_application.message_sent', $application, ['reference' => $application->reference]);

        $application->student->portalUser()?->notify(new StudentApplicationMessageReceived($message));

        return redirect()->route('admin.student-applications.show', $application)->with('status', 'Message sent to the student.');
    }
}
