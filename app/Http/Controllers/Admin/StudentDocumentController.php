<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentDocumentType;
use App\Http\Controllers\Controller;
use App\Models\StudentApplication;
use App\Models\StudentDocument;
use App\Services\Consultancy\StudentDocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function __construct(private StudentDocumentStore $documents) {}

    public function store(Request $request, StudentApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);
        $data = $request->validate([
            'document_type' => ['required', Rule::enum(StudentDocumentType::class)],
            'document' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(StudentDocumentStore::MAX_KILOBYTES)],
        ]);

        $this->documents->store($application, $request->file('document'), StudentDocumentType::from($data['document_type']), $request->user());

        return redirect()->route('admin.student-applications.show', $application)->with('status', 'Document uploaded.');
    }

    public function download(StudentApplication $application, StudentDocument $document): StreamedResponse
    {
        Gate::authorize('view', $application);
        $this->ensureBelongs($application, $document);

        return $this->documents->download($document);
    }

    public function review(Request $request, StudentApplication $application, StudentDocument $document): RedirectResponse
    {
        Gate::authorize('verifyDocuments', $application);
        $this->ensureBelongs($application, $document);
        $data = $request->validate([
            'status' => ['required', Rule::in([StudentDocument::STATUS_VERIFIED, StudentDocument::STATUS_REJECTED])],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->documents->review($document, $data['status'], $request->user(), $data['review_note'] ?? null);

        return redirect()->route('admin.student-applications.show', $application)->with('status', 'Document review saved.');
    }

    public function destroy(Request $request, StudentApplication $application, StudentDocument $document): RedirectResponse
    {
        Gate::authorize('update', $application);
        $this->ensureBelongs($application, $document);
        $this->documents->delete($document, $request->user());

        return redirect()->route('admin.student-applications.show', $application)->with('status', 'Document deleted.');
    }

    private function ensureBelongs(StudentApplication $application, StudentDocument $document): void
    {
        abort_unless((int) $document->student_application_id === (int) $application->getKey(), 404);
    }
}
