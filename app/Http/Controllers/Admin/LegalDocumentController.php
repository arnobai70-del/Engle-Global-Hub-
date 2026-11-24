<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Services\ActivityRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LegalDocumentController extends Controller
{
    public function __construct(private ActivityRecorder $activity) {}

    public function index(): View
    {
        return view('admin.legal-documents.index', [
            'documents' => LegalDocument::query()->latest('id')->paginate(25),
            'types' => LegalDocument::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(true));
        $upload = $request->file('original_pdf');
        $hash = hash_file('sha256', $upload->getRealPath());

        if (LegalDocument::query()->where('private_sha256', $hash)->exists()) {
            return back()->withInput()->withErrors(['original_pdf' => 'This exact document has already been uploaded.']);
        }

        $path = $upload->store('originals', config('legal-documents.disk'));
        try {
            $document = new LegalDocument;
            $document->fill($this->metadata($data));
            $document->private_path = $path;
            $document->private_sha256 = $hash;
            $document->uploaded_by_user_id = $request->user()->getKey();
            $document->verification_status = 'pending';
            $document->publication_status = 'draft';
            $document->save();
        } catch (\Throwable $error) {
            Storage::disk(config('legal-documents.disk'))->delete($path);
            throw $error;
        }

        $this->activity->record($request->user(), 'legal_document.created', $document);

        return redirect()->route('admin.legal-documents.index')->with('status', 'Original stored privately as a draft. Owner verification is required.');
    }

    public function update(Request $request, LegalDocument $document): RedirectResponse
    {
        $data = $request->validate($this->rules(false));
        $newCopyPath = null;

        if ($request->hasFile('redacted_pdf')) {
            $newCopyPath = $request->file('redacted_pdf')->store('redacted', config('legal-documents.disk'));
        }

        try {
            $document->fill($this->metadata($data));
            if ($newCopyPath !== null) {
                $document->public_copy_path = $newCopyPath;
                $document->public_copy_sha256 = hash_file('sha256', $request->file('redacted_pdf')->getRealPath());
            }
            if ($request->boolean('remove_redacted_copy')) {
                $document->public_copy_path = null;
                $document->public_copy_sha256 = null;
            }
            // Every edit retracts publication and owner approval. No old public URL remains authorised.
            $document->redaction_confirmed = false;
            $document->approved_at = null;
            $document->approved_by_user_id = null;
            $document->verified_at = null;
            $document->verification_status = 'pending';
            $document->published_at = null;
            $document->publication_status = 'draft';
            $document->save();
        } catch (\Throwable $error) {
            if ($newCopyPath !== null) {
                Storage::disk(config('legal-documents.disk'))->delete($newCopyPath);
            }
            throw $error;
        }

        $this->activity->record($request->user(), 'legal_document.revised', $document);

        return back()->with('status', 'Changes saved. Owner approval and publication have been reset.');
    }

    public function original(LegalDocument $document)
    {
        abort_unless(Storage::disk(config('legal-documents.disk'))->exists($document->private_path), 404);
        $this->activity->record(request()->user(), 'legal_document.original_downloaded', $document);

        return Storage::disk(config('legal-documents.disk'))->download(
            $document->private_path,
            'legal-original-'.$document->id.'.pdf',
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function redacted(LegalDocument $document)
    {
        abort_unless($document->public_copy_path !== null, 404);
        abort_unless(Storage::disk(config('legal-documents.disk'))->exists($document->public_copy_path), 404);

        return Storage::disk(config('legal-documents.disk'))->download(
            $document->public_copy_path,
            'legal-redacted-'.$document->id.'.pdf',
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
        );
    }

    public function approve(Request $request, LegalDocument $document): RedirectResponse
    {
        $request->validate([
            'confirm_official_source' => ['accepted'],
            'confirm_redacted_copy' => $document->public_copy_path !== null ? ['accepted'] : ['prohibited'],
        ]);

        abort_unless(Storage::disk(config('legal-documents.disk'))->exists($document->private_path), 422);
        if ($document->public_copy_path !== null) {
            abort_unless(Storage::disk(config('legal-documents.disk'))->exists($document->public_copy_path), 422);
        }
        if ($document->expires_at !== null && $document->expires_at->isBefore(today())) {
            return back()->withErrors(['document' => 'Expired documents cannot be approved or published.']);
        }

        $document->verification_status = 'verified';
        $document->verified_at = today();
        $document->approved_by_user_id = $request->user()->getKey();
        $document->approved_at = now();
        $document->redaction_confirmed = $document->public_copy_path !== null;
        $document->publication_status = 'draft';
        $document->save();

        $this->activity->record($request->user(), 'legal_document.approved', $document);

        return back()->with('status', 'Verified and approved. Still private until separately published.');
    }

    public function publish(Request $request, LegalDocument $document): RedirectResponse
    {
        if (! $document->canPublish()
            || ! Storage::disk(config('legal-documents.disk'))->exists($document->private_path)
            || ($document->public_copy_path !== null && ! Storage::disk(config('legal-documents.disk'))->exists($document->public_copy_path))) {
            return back()->withErrors(['document' => 'Only verified, owner-approved, current documents with available files can be published.']);
        }

        $document->publication_status = 'published';
        $document->published_at = now();
        $document->save();
        $this->activity->record($request->user(), 'legal_document.published', $document);

        return back()->with('status', 'Public metadata is live. The original PDF remains private.');
    }

    public function unpublish(Request $request, LegalDocument $document): RedirectResponse
    {
        $document->publication_status = 'draft';
        $document->published_at = null;
        $document->save();
        $this->activity->record($request->user(), 'legal_document.unpublished', $document);

        return back()->with('status', 'Removed from the public document listing.');
    }

    private function rules(bool $creating): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'document_type' => ['required', Rule::in(array_keys(LegalDocument::TYPES))],
            'issuer' => ['required', 'string', 'max:180'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'issued_at' => ['nullable', 'date_format:Y-m-d'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:issued_at'],
            'original_pdf' => $creating ? ['required', File::types(['pdf'])->max(config('legal-documents.max_pdf_kb'))] : ['prohibited'],
            'redacted_pdf' => $creating ? ['prohibited'] : ['nullable', File::types(['pdf'])->max(config('legal-documents.max_pdf_kb'))],
            'remove_redacted_copy' => ['sometimes', 'boolean'],
        ];
    }

    private function metadata(array $data): array
    {
        return collect($data)->only([
            'title', 'document_type', 'issuer', 'reference_number', 'issued_at', 'expires_at',
        ])->map(fn ($value) => is_string($value) ? trim(strip_tags($value)) : $value)->all();
    }
}
