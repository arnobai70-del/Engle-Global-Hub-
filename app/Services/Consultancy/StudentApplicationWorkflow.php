<?php

namespace App\Services\Consultancy;

use App\Enums\StudentApplicationStatus;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Notifications\StudentApplicationStatusChanged;
use App\Services\ActivityRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The only place a student application is opened or changes status, so every
 * change is validated against the allowed transitions and audited.
 */
class StudentApplicationWorkflow
{
    public function __construct(private ActivityRecorder $activity) {}

    /**
     * @param  array{destination_country_id: int, institution_id?: int|null, course_name?: string|null, intake?: string|null, assigned_consultant_id?: int|null, student_enquiry_id?: int|null}  $attributes
     */
    public function open(Student $student, array $attributes, ?User $actor): StudentApplication
    {
        return DB::transaction(function () use ($student, $attributes, $actor): StudentApplication {
            $application = new StudentApplication([
                ...$attributes,
                'student_id' => $student->getKey(),
                'reference' => $this->newReference(),
            ]);
            $application->status = StudentApplicationStatus::NewEnquiry;
            $application->save();

            $application->statusChanges()->create([
                'from_status' => null,
                'to_status' => StudentApplicationStatus::NewEnquiry,
                'changed_by_user_id' => $actor?->getKey(),
                'student_note' => 'Your application file has been opened.',
            ]);

            $this->activity->record($actor, 'student_application.opened', $application, ['reference' => $application->reference]);

            return $application;
        });
    }

    /**
     * @throws ValidationException when the transition is not allowed from the current status
     */
    public function transition(
        StudentApplication $application,
        StudentApplicationStatus $target,
        User $actor,
        ?string $studentNote = null,
        ?string $internalNote = null,
    ): StudentApplication {
        DB::transaction(function () use ($application, $target, $actor, $studentNote, $internalNote): void {
            $locked = StudentApplication::query()->lockForUpdate()->findOrFail($application->getKey());
            $current = $locked->status;

            if (! $current->canTransitionTo($target)) {
                throw ValidationException::withMessages([
                    'status' => "An application cannot move from \"{$current->label()}\" to \"{$target->label()}\".",
                ]);
            }

            $locked->status = $target;
            $locked->save();

            $locked->statusChanges()->create([
                'from_status' => $current,
                'to_status' => $target,
                'changed_by_user_id' => $actor->getKey(),
                'student_note' => $studentNote,
                'internal_note' => $internalNote,
            ]);

            $this->activity->record($actor, 'student_application.status_changed', $locked, [
                'reference' => $locked->reference,
                'from' => $current->value,
                'to' => $target->value,
            ]);

            $application->setRawAttributes($locked->getAttributes(), true);
        });

        $application->student->portalUser()?->notify(
            new StudentApplicationStatusChanged($application, $target->label(), $studentNote),
        );

        return $application;
    }

    /**
     * Assigns or reassigns the consultant responsible for an application.
     */
    public function assignConsultant(StudentApplication $application, ?User $consultant, User $actor): void
    {
        if ((int) $application->assigned_consultant_id === (int) $consultant?->getKey()) {
            return;
        }

        $application->forceFill(['assigned_consultant_id' => $consultant?->getKey()])->save();
        $this->activity->record($actor, 'student_application.consultant_assigned', $application, [
            'reference' => $application->reference,
            'consultant_id' => $consultant?->getKey(),
        ]);
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-SA-'.Str::upper(Str::random(10));
        } while (StudentApplication::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
