<?php

namespace App\Http\Resources\V1;

use App\Models\StudentApplication;
use App\Models\StudentApplicationStatusChange;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Student-facing view of an application. Internal notes, staff identities
 * and file paths are never included.
 *
 * @mixin StudentApplication
 */
class StudentApplicationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => ['code' => $this->status->value, 'label' => $this->status->label(), 'stage' => $this->status->stage(), 'closed' => $this->status->isClosed()],
            'destination' => $this->destinationCountry?->name,
            'institution' => $this->institution?->name,
            'course' => $this->course_name,
            'intake' => $this->intake,
            'consultant_assigned' => $this->assigned_consultant_id !== null,
            'updated_at' => $this->updated_at?->toAtomString(),
            'timeline' => $this->whenLoaded('statusChanges', fn () => $this->statusChanges->map(fn (StudentApplicationStatusChange $change): array => [
                'status' => $change->to_status->label(),
                'note' => $change->student_note,
                'at' => $change->created_at?->toAtomString(),
            ])->values()),
            'documents' => $this->whenLoaded('documents', fn () => $this->documents->map(fn (StudentDocument $document): array => [
                'id' => $document->id,
                'type' => $document->document_type->label(),
                'name' => $document->original_name,
                'status' => $document->statusLabel(),
                'review_note' => $document->review_note,
                'uploaded_at' => $document->created_at?->toAtomString(),
            ])->values()),
        ];
    }
}
