<?php

namespace App\Services\Consultancy;

use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;

/**
 * Resolves which student profile a portal login owns, and guards access to
 * that student's applications.
 */
class StudentPortal
{
    /**
     * The profile explicitly linked to this login, or else an unlinked profile
     * with the same verified email address.
     */
    public function studentFor(User $user): ?Student
    {
        return Student::query()->where('user_id', $user->getKey())->first()
            ?? ($user->hasVerifiedEmail()
                ? Student::query()->whereNull('user_id')->where('email', strtolower($user->email))->first()
                : null);
    }

    public function owns(User $user, StudentApplication $application): bool
    {
        $student = $this->studentFor($user);

        return $student !== null && (int) $application->student_id === (int) $student->getKey();
    }

    public function authorize(User $user, StudentApplication $application): void
    {
        abort_unless($this->owns($user, $application), 403);
    }
}
