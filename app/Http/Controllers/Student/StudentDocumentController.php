<?php

namespace App\Http\Controllers\Student;

use App\Enums\StudentDocumentType;
use App\Http\Controllers\Controller;
use App\Models\StudentApplication;
use App\Models\StudentDocument;
use App\Services\Consultancy\StudentDocumentStore;
use App\Services\Consultancy\StudentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function __construct(private StudentPortal $portal, private StudentDocumentStore $documents) {}

    public function store(Request $request, StudentApplication $application): RedirectResponse
    {
        $this->portal->authorize($request->user(), $application);

        $data = $request->validate([
            'document_type' => ['required', Rule::enum(StudentDocumentType::class)],
            'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(StudentDocumentStore::MAX_KILOBYTES)],
        ]);

        $this->documents->store(
            $application,
            $request->file('document'),
            StudentDocumentType::from($data['document_type']),
            $request->user(),
        );

        return redirect()->route('student.applications.show', $application)
            ->with('status', 'Document uploaded. Your consultant will review it.');
    }

    public function download(Request $request, StudentApplication $application, StudentDocument $document): StreamedResponse
    {
        $this->portal->authorize($request->user(), $application);
        abort_unless((int) $document->student_application_id === (int) $application->getKey(), 404);

        return $this->documents->download($document);
    }

    public function destroy(Request $request, StudentApplication $application, StudentDocument $document): RedirectResponse
    {
        $this->portal->authorize($request->user(), $application);
        abort_unless((int) $document->student_application_id === (int) $application->getKey(), 404);

        if ($document->status === StudentDocument::STATUS_VERIFIED) {
            return back()->withErrors(['document' => 'A verified document cannot be removed. Please contact your consultant.']);
        }

        $this->documents->delete($document, $request->user());

        return redirect()->route('student.applications.show', $application)->with('status', 'Document removed.');
    }
}
