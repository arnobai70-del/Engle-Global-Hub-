<?php

namespace App\Services\Consultancy;

use App\Enums\StudentDocumentType;
use App\Models\StudentApplication;
use App\Models\StudentDocument;
use App\Models\User;
use App\Notifications\StudentDocumentReviewed;
use App\Services\ActivityRecorder;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Stores, reviews and serves student documents on the private disk.
 *
 * Files get random names, their type is taken from the file content (not the
 * client-supplied name), and the same file cannot be attached twice to one
 * application.
 */
class StudentDocumentStore
{
    public const DISK = 'student_documents';

    /** Maximum upload size in kilobytes (10 MB). */
    public const MAX_KILOBYTES = 10240;

    /** @var array<string, string> mime type => stored extension */
    public const ALLOWED_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function __construct(private ActivityRecorder $activity) {}

    /**
     * @throws ValidationException when the application is closed or the file is a duplicate
     */
    public function store(StudentApplication $application, UploadedFile $file, StudentDocumentType $type, User $uploader): StudentDocument
    {
        if (! $application->acceptsUploads()) {
            throw ValidationException::withMessages(['document' => 'This application is closed and no longer accepts documents.']);
        }

        $mimeType = (string) $file->getMimeType();
        $extension = self::ALLOWED_TYPES[$mimeType] ?? null;
        if ($extension === null) {
            throw ValidationException::withMessages(['document' => 'Only PDF, JPG and PNG files are accepted.']);
        }

        $checksum = hash_file('sha256', $file->getRealPath());
        if ($application->documents()->where('sha256', $checksum)->exists()) {
            throw ValidationException::withMessages(['document' => 'This file has already been uploaded to this application.']);
        }

        $storedPath = $this->disk()->putFileAs(
            (string) $application->getKey(),
            $file,
            Str::uuid()->toString().'.'.$extension,
        );

        try {
            $document = DB::transaction(function () use ($application, $type, $file, $storedPath, $mimeType, $checksum, $uploader): StudentDocument {
                $document = $application->documents()->create([
                    'document_type' => $type,
                    'original_name' => $this->safeDisplayName($file->getClientOriginalName(), $type),
                    'stored_path' => $storedPath,
                    'mime_type' => $mimeType,
                    'size_bytes' => (int) $file->getSize(),
                    'sha256' => $checksum,
                    'uploaded_by_user_id' => $uploader->getKey(),
                ]);
                $document->status = StudentDocument::STATUS_PENDING_REVIEW;

                $this->activity->record($uploader, 'student_document.uploaded', $document, [
                    'application' => $application->reference,
                    'type' => $type->value,
                ]);

                return $document;
            });
        } catch (Throwable $exception) {
            $this->disk()->delete($storedPath);

            throw $exception;
        }

        return $document;
    }

    /**
     * @throws ValidationException when a rejection has no reason
     */
    public function review(StudentDocument $document, string $status, User $reviewer, ?string $note): StudentDocument
    {
        if ($status === StudentDocument::STATUS_REJECTED && blank($note)) {
            throw ValidationException::withMessages(['review_note' => 'Tell the student why the document was rejected.']);
        }

        DB::transaction(function () use ($document, $status, $reviewer, $note): void {
            $document->forceFill([
                'status' => $status,
                'reviewed_by_user_id' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_note' => $note,
            ])->save();

            $this->activity->record($reviewer, 'student_document.'.$status, $document, [
                'application' => $document->application->reference,
            ]);
        });

        $document->application->student->portalUser()?->notify(new StudentDocumentReviewed($document));

        return $document;
    }

    public function delete(StudentDocument $document, User $actor): void
    {
        DB::transaction(function () use ($document, $actor): void {
            $this->activity->record($actor, 'student_document.deleted', $document, [
                'application' => $document->application->reference,
                'type' => $document->document_type->value,
            ]);
            $document->delete();
        });

        $this->disk()->delete($document->stored_path);
    }

    public function download(StudentDocument $document): StreamedResponse
    {
        $extension = self::ALLOWED_TYPES[$document->mime_type] ?? 'bin';
        $downloadName = Str::slug(pathinfo($document->original_name, PATHINFO_FILENAME)) ?: 'document';

        return $this->disk()->download($document->stored_path, $downloadName.'.'.$extension, [
            'Content-Type' => $document->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    private function safeDisplayName(string $clientName, StudentDocumentType $type): string
    {
        $name = preg_replace('/[\x00-\x1F\x7F<>:"\/\\\\|?*]+/u', '', basename($clientName)) ?? '';
        $name = trim($name);

        return $name === '' ? $type->label() : Str::limit($name, 200, '');
    }
}
