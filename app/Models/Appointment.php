<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A consultation appointment with a student or lead.
 */
class Appointment extends Model
{
    public const STATUS_REQUESTED = 'requested';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    /** @var array<string, string> */
    public const STATUSES = [
        self::STATUS_REQUESTED => 'Requested',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_NO_SHOW => 'No show',
    ];

    /** @var array<string, string> */
    public const MODES = [
        'office' => 'In our office',
        'phone' => 'Phone call',
        'video' => 'Video call',
    ];

    /** @var list<string> */
    protected $fillable = [
        'student_enquiry_id', 'student_application_id', 'staff_user_id', 'scheduled_at',
        'duration_minutes', 'mode', 'location_or_link', 'student_note',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'duration_minutes' => 'integer'];
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(StudentEnquiry::class, 'student_enquiry_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(StudentApplication::class, 'student_application_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
