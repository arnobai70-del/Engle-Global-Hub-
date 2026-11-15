<?php

namespace App\Http\Controllers\Student;

use App\Enums\StudentApplicationStatus;
use App\Enums\StudentDocumentType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PaymentRecord;
use App\Models\StudentApplication;
use App\Models\StudentApplicationMessage;
use App\Models\StudentApplicationStatusChange;
use App\Models\StudentDocument;
use App\Services\Consultancy\StudentDocumentStore;
use App\Services\Consultancy\StudentPortal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Application tracking for a signed-in student.
 *
 * Only student-facing data is passed to these views: internal notes and staff
 * identities never reach them.
 */
class StudentApplicationController extends Controller
{
    /** Stages in the order a successful application passes through them. */
    private const PROGRESS_STAGES = ['Consultation', 'Admission', "Student's Pass", 'Closed'];

    public function __construct(private StudentPortal $portal) {}

    public function index(Request $request): View
    {
        $student = $this->portal->studentFor($request->user());

        $applications = $student === null
            ? collect()
            : $student->applications()->with(['destinationCountry:id,name', 'institution:id,name'])->latest('updated_at')->get();

        return view('student.applications.index', compact('student', 'applications'));
    }

    public function show(Request $request, StudentApplication $application): View
    {
        $this->portal->authorize($request->user(), $application);

        $application->load(['destinationCountry:id,name', 'institution:id,name', 'assignedConsultant:id,name']);

        $timeline = $application->statusChanges()
            ->get(['id', 'to_status', 'student_note', 'created_at'])
            ->map(fn (StudentApplicationStatusChange $change): array => [
                'status' => $change->to_status->label(),
                'note' => $change->student_note,
                'date' => $change->created_at,
            ]);

        $documents = $application->documents()
            ->get(['id', 'student_application_id', 'document_type', 'original_name', 'status', 'review_note', 'size_bytes', 'created_at']);

        $messages = $application->messages()
            ->get(['id', 'author_type', 'body', 'created_at'])
            ->map(fn (StudentApplicationMessage $message): array => [
                'from' => $message->author_type === StudentApplicationMessage::AUTHOR_STUDENT ? 'You' : 'Eagle Global Hub',
                'body' => $message->body,
                'date' => $message->created_at,
            ]);

        return view('student.applications.show', [
            'application' => $application,
            'timeline' => $timeline,
            'documents' => $documents,
            'checklist' => $this->checklist($documents),
            'messages' => $messages,
            'progress' => $this->progress($application->status),
            'appointments' => $application->appointments()->whereIn('status', [Appointment::STATUS_REQUESTED, Appointment::STATUS_CONFIRMED])->where('scheduled_at', '>=', now()->subDay())->get(['id', 'scheduled_at', 'mode', 'status', 'location_or_link']),
            'payments' => $application->payments()->where('status', PaymentRecord::STATUS_VERIFIED)->get(['id', 'reference', 'amount', 'currency', 'method', 'paid_on', 'purpose']),
            'appointmentModes' => Appointment::MODES,
            'documentTypes' => StudentDocumentType::cases(),
            'maxUploadMegabytes' => StudentDocumentStore::MAX_KILOBYTES / 1024,
        ]);
    }

    /**
     * Latest status for each document category the student has uploaded.
     *
     * @param  Collection<int, StudentDocument>  $documents
     * @return list<array{label: string, status: string}>
     */
    private function checklist($documents): array
    {
        return collect(StudentDocumentType::cases())
            ->map(function (StudentDocumentType $type) use ($documents): array {
                $latest = $documents->first(fn (StudentDocument $document): bool => $document->document_type === $type);

                return ['label' => $type->label(), 'status' => $latest?->statusLabel() ?? 'Not uploaded'];
            })
            ->all();
    }

    /**
     * @return list<array{stage: string, state: string}>
     */
    private function progress(StudentApplicationStatus $status): array
    {
        $current = array_search($status->stage(), self::PROGRESS_STAGES, true);

        return array_map(fn (string $stage, int $index): array => [
            'stage' => $stage,
            'state' => match (true) {
                $current === false => 'pending',
                $index < $current => 'done',
                $index === $current => 'current',
                default => 'pending',
            },
        ], self::PROGRESS_STAGES, array_keys(self::PROGRESS_STAGES));
    }
}
