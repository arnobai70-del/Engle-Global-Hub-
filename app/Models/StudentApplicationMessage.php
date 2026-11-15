<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message between the student and Eagle Global Hub staff on one application.
 * Both sides can read every message; staff-only notes live elsewhere.
 */
class StudentApplicationMessage extends Model
{
    public const UPDATED_AT = null;

    public const AUTHOR_STUDENT = 'student';

    public const AUTHOR_STAFF = 'staff';

    /** @var list<string> */
    protected $fillable = ['student_application_id', 'author_user_id', 'author_type', 'body'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
