<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LegalDocumentController extends Controller
{
    public function index(): View
    {
        return view('public.legal-documents', [
            'documents' => LegalDocument::query()->publiclyVisible()->orderBy('document_type')->latest('issued_at')->get(),
        ]);
    }

    public function download(LegalDocument $legalDocument)
    {
        abort_unless(LegalDocument::query()->publiclyVisible()->whereKey($legalDocument->getKey())->exists(), 404);
        abort_unless($legalDocument->public_copy_path !== null && $legalDocument->redaction_confirmed, 404);

        $disk = Storage::disk(config('legal-documents.disk'));
        abort_unless($disk->exists($legalDocument->public_copy_path), 404);

        // The unredacted original can NEVER be served by this route.
        return $disk->download($legalDocument->public_copy_path, 'verified-redacted-'.$legalDocument->id.'.pdf', [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
