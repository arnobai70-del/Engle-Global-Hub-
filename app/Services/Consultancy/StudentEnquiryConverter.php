<?php

namespace App\Services\Consultancy;

use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\StudentEnquiry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Converts a qualified enquiry into a student profile and an application.
 *
 * Students are matched on email only, which is unique per profile, so two
 * different people are never merged on name or phone similarity.
 */
class StudentEnquiryConverter
{
    public function __construct(private StudentApplicationWorkflow $workflow) {}

    /**
     * @param  array{destination_country_id: int, institution_id?: int|null, course_name?: string|null, intake?: string|null, assigned_consultant_id?: int|null}  $applicationAttributes
     *
     * @throws ValidationException when the enquiry has already been converted
     */
    public function convert(StudentEnquiry $enquiry, array $applicationAttributes, User $actor): StudentApplication
    {
        return DB::transaction(function () use ($enquiry, $applicationAttributes, $actor): StudentApplication {
            $locked = StudentEnquiry::query()->lockForUpdate()->findOrFail($enquiry->getKey());

            if ($locked->status === StudentEnquiry::STATUS_CONVERTED) {
                throw ValidationException::withMessages([
                    'enquiry' => 'This enquiry has already been converted to a student application.',
                ]);
            }

            $student = $this->findOrCreateStudent($locked);

            $application = $this->workflow->open($student, [
                ...$applicationAttributes,
                'student_enquiry_id' => $locked->getKey(),
            ], $actor);

            $locked->forceFill([
                'status' => StudentEnquiry::STATUS_CONVERTED,
                'student_id' => $student->getKey(),
            ])->save();
            $enquiry->setRawAttributes($locked->getAttributes(), true);

            return $application;
        });
    }

    private function findOrCreateStudent(StudentEnquiry $enquiry): Student
    {
        $email = Str::lower(trim($enquiry->email));
        $student = Student::query()->where('email', $email)->first();

        if ($student === null) {
            [$firstName, $lastName] = $this->splitName($enquiry->full_name);
            $student = Student::query()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $enquiry->phone,
                'reference_code' => $this->newStudentReference(),
                'status' => Student::STATUS_ACTIVE,
            ]);
        }

        $this->linkVerifiedPortalUser($student);

        return $student;
    }

    /**
     * Links the student's portal login only when a verified account owns the
     * same email and is not already linked to another profile.
     */
    private function linkVerifiedPortalUser(Student $student): void
    {
        if ($student->user_id !== null) {
            return;
        }

        $user = User::query()
            ->where('email', $student->email)
            ->whereNotNull('email_verified_at')
            ->first();

        if ($user === null || Student::query()->where('user_id', $user->getKey())->exists()) {
            return;
        }

        $student->forceFill(['user_id' => $user->getKey()])->save();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        if (count($parts) < 2) {
            return [Str::limit(trim($fullName), 100, ''), ''];
        }

        $lastName = array_pop($parts);

        return [Str::limit(implode(' ', $parts), 100, ''), Str::limit($lastName, 100, '')];
    }

    private function newStudentReference(): string
    {
        do {
            $reference = 'EGH-ST-'.Str::upper(Str::random(10));
        } while (Student::query()->where('reference_code', $reference)->exists());

        return $reference;
    }
}
