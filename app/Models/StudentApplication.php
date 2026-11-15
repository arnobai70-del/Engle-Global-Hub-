<?php

namespace App\Models;

use App\Enums\StudentApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * One student's application to study in a destination country.
 *
 * Status changes must go through StudentApplicationWorkflow so every change is
 * validated and recorded in the audit trail.
 */
class StudentApplication extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'reference', 'student_id', 'student_enquiry_id', 'destination_country_id',
        'institution_id', 'course_id', 'course_intake_id', 'course_name', 'intake', 'assigned_consultant_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StudentApplicationStatus::class,
            'student_id' => 'integer',
            'destination_country_id' => 'integer',
            'institution_id' => 'integer',
            'course_id' => 'integer',
            'course_intake_id' => 'integer',
            'assigned_consultant_id' => 'integer',
            'follow_up_at' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(StudentEnquiry::class, 'student_enquiry_id');
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function assignedConsultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_consultant_id');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(StudentApplicationStatusChange::class)->orderBy('created_at')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class)->latest('id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StudentApplicationMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function acceptsUploads(): bool
    {
        return ! $this->status->isClosed();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseIntake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('scheduled_at');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(PaymentRecord::class, 'payable')->latest('paid_on');
    }
}
