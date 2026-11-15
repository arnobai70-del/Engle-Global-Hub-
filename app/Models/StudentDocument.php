<?php

namespace App\Models;

use App\Enums\StudentDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A privately stored file attached to a student application.
 */
class StudentDocument extends Model
{
    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_REVIEW => 'Pending review',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_REJECTED => 'Rejected — please re-upload',
    ];

    /** @var list<string> */
    protected $fillable = [
        'student_application_id', 'document_type', 'original_name', 'stored_path',
        'mime_type', 'size_bytes', 'sha256', 'uploaded_by_user_id',
    ];

    /** @var list<string> */
    protected $hidden = ['stored_path', 'sha256'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'document_type' => StudentDocumentType::class,
            'size_bytes' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
