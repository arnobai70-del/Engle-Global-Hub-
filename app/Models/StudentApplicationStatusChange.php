<?php

namespace App\Models;

use App\Enums\StudentApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only audit record of a student application status change.
 */
class StudentApplicationStatusChange extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'student_application_id', 'from_status', 'to_status',
        'changed_by_user_id', 'student_note', 'internal_note',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'from_status' => StudentApplicationStatus::class,
            'to_status' => StudentApplicationStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
