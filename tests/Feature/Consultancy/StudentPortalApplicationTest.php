<?php

namespace Tests\Feature\Consultancy;

use App\Enums\StudentApplicationStatus;
use App\Models\Country;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    private function applicationFor(string $email, ?User $linkedUser = null): StudentApplication
    {
        $student = Student::query()->create([
            'first_name' => 'Test', 'last_name' => 'Student', 'email' => $email,
            'reference_code' => 'EGH-ST-'.strtoupper(substr(md5($email), 0, 10)), 'status' => Student::STATUS_ACTIVE,
        ]);
        if ($linkedUser !== null) {
            $student->forceFill(['user_id' => $linkedUser->id])->save();
        }

        $workflow = app(StudentApplicationWorkflow::class);
        $admin = $this->user('admin');
        $application = $workflow->open($student, [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
            'course_name' => 'Diploma in Hospitality',
        ], $admin);
        $workflow->transition(
            $application,
            StudentApplicationStatus::EligibilityReview,
            $admin,
            'Please send your HSC transcript.',
            'SECRET-INTERNAL: agent commission discussion',
        );

        return $application;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('student.applications.index'))->assertRedirect(route('login'));
    }

    public function test_linked_student_sees_own_timeline_without_internal_notes(): void
    {
        $user = $this->user('customer', ['email' => 'student@example.com']);
        $application = $this->applicationFor('student@example.com', $user);

        $this->actingAs($user)->get(route('student.applications.index'))
            ->assertOk()->assertSee($application->reference)->assertSee('Eligibility Review');

        $this->actingAs($user)->get(route('student.applications.show', $application))
            ->assertOk()
            ->assertSee('Diploma in Hospitality')
            ->assertSee('Please send your HSC transcript.')
            ->assertSee('Your application file has been opened.')
            ->assertDontSee('SECRET-INTERNAL')
            ->assertSee('noindex,nofollow', false);
    }

    public function test_verified_user_with_matching_email_sees_unlinked_profile(): void
    {
        $user = $this->user('customer', ['email' => 'match@example.com']);
        $application = $this->applicationFor('match@example.com');

        $this->actingAs($user)->get(route('student.applications.show', $application))->assertOk();
    }

    public function test_student_cannot_view_another_students_application(): void
    {
        $owner = $this->user('customer', ['email' => 'owner@example.com']);
        $other = $this->user('customer', ['email' => 'other@example.com']);
        $application = $this->applicationFor('owner@example.com', $owner);

        $this->actingAs($other)->get(route('student.applications.show', $application))->assertForbidden();
        $this->actingAs($other)->get(route('student.applications.index'))
            ->assertOk()->assertDontSee($application->reference)->assertSee('No student profile is linked');
    }

    public function test_profile_linked_to_another_login_is_not_claimed_by_email(): void
    {
        $owner = $this->user('customer', ['email' => 'linked-owner@example.com']);
        $application = $this->applicationFor('shared@example.com', $owner);
        $sameEmail = $this->user('customer', ['email' => 'shared@example.com']);

        $this->actingAs($sameEmail)->get(route('student.applications.show', $application))->assertForbidden();
    }
}
