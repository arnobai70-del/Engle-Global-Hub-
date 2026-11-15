<?php

namespace Tests\Feature\Consultancy;

use App\Models\Appointment;
use App\Models\Country;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\User;
use App\Notifications\AppointmentUpdated;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CourseAndAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $singaporeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Notification::fake();
        $this->admin = $this->user('admin');
        $this->singaporeId = (int) Country::query()->where('iso2', 'SG')->value('id');
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    private function institution(): Institution
    {
        return Institution::query()->create(['name' => 'Example Institute Singapore', 'country_id' => $this->singaporeId, 'status' => Institution::STATUS_ACTIVE]);
    }

    private function application(?User $portalUser = null): StudentApplication
    {
        $student = Student::query()->create([
            'first_name' => 'Ayesha', 'last_name' => 'Rahman', 'email' => $portalUser?->email ?? 'ayesha@example.com',
            'reference_code' => 'EGH-ST-COURSE0001', 'status' => Student::STATUS_ACTIVE,
        ]);
        $student->forceFill(['user_id' => $portalUser?->id])->save();

        return app(StudentApplicationWorkflow::class)->open($student, [
            'destination_country_id' => $this->singaporeId, 'assigned_consultant_id' => $this->admin->id,
        ], $this->admin);
    }

    public function test_course_catalogue_with_intakes_and_duplicate_prevention(): void
    {
        $institution = $this->institution();

        $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'institution_id' => $institution->id, 'name' => 'Diploma in Business', 'study_level' => 'diploma', 'duration_months' => 24,
        ])->assertRedirect();
        $course = Course::query()->sole();

        $this->actingAs($this->admin)->post(route('admin.courses.store'), ['institution_id' => $institution->id, 'name' => 'Diploma in Business'])
            ->assertSessionHasErrors('name');
        $this->actingAs($this->admin)->post(route('admin.courses.store'), ['institution_id' => $institution->id, 'name' => 'Bad', 'source_url' => 'http://insecure.test'])
            ->assertSessionHasErrors('source_url');

        $this->actingAs($this->admin)->post(route('admin.courses.intakes.store', $course), ['starts_on' => '2027-01', 'application_deadline' => '2026-12-01'])->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.courses.intakes.store', $course), ['starts_on' => '2027-01'])->assertSessionHasErrors('starts_on');
        $this->assertSame(1, $course->intakes()->count());

        $this->actingAs($this->admin)->get(route('admin.courses.index'))->assertOk()->assertSee('Diploma in Business');
        $this->actingAs($this->user('crm-staff'))->get(route('admin.courses.index'))->assertForbidden();
    }

    public function test_choosing_a_catalogue_course_fills_application_details(): void
    {
        $institution = $this->institution();
        $course = Course::query()->create(['institution_id' => $institution->id, 'name' => 'BSc Computing', 'is_active' => true]);
        $intake = $course->intakes()->create(['starts_on' => '2027-08-01', 'is_open' => true]);
        $otherCourse = Course::query()->create(['institution_id' => $institution->id, 'name' => 'Other', 'is_active' => true]);
        $otherIntake = $otherCourse->intakes()->create(['starts_on' => '2027-01-01', 'is_open' => true]);
        $application = $this->application();

        $this->actingAs($this->admin)->patch(route('admin.student-applications.update', $application), [
            'destination_country_id' => $this->singaporeId, 'course_id' => $course->id, 'course_intake_id' => $otherIntake->id,
        ])->assertSessionHasErrors('course_intake_id');

        $this->actingAs($this->admin)->patch(route('admin.student-applications.update', $application), [
            'destination_country_id' => $this->singaporeId, 'course_id' => $course->id, 'course_intake_id' => $intake->id,
            'assigned_consultant_id' => $this->admin->id,
        ])->assertRedirect();

        $application->refresh();
        $this->assertSame('BSc Computing', $application->course_name);
        $this->assertSame('August 2027', $application->intake);
        $this->assertSame($institution->id, $application->institution_id);
    }

    public function test_staff_booking_prevents_double_booking_and_notifies_student(): void
    {
        $studentUser = $this->user('customer', ['email' => 'student@example.com']);
        $application = $this->application($studentUser);
        $slot = now()->addDays(3)->setTime(10, 0)->format('Y-m-d H:i');

        $this->actingAs($this->admin)->post(route('admin.appointments.store'), [
            'student_application_id' => $application->id, 'staff_user_id' => $this->admin->id, 'scheduled_at' => $slot,
            'duration_minutes' => 30, 'mode' => 'office', 'status' => 'confirmed', 'location_or_link' => 'Gulshan office',
        ])->assertSessionHasNoErrors();

        $this->actingAs($this->admin)->post(route('admin.appointments.store'), [
            'student_application_id' => $application->id, 'staff_user_id' => $this->admin->id,
            'scheduled_at' => now()->addDays(3)->setTime(10, 15)->format('Y-m-d H:i'),
            'duration_minutes' => 30, 'mode' => 'phone', 'status' => 'confirmed',
        ])->assertSessionHasErrors('scheduled_at');
        $this->assertSame(1, Appointment::query()->count());

        $this->actingAs($this->admin)->post(route('admin.appointments.store'), [
            'student_application_id' => $application->id, 'scheduled_at' => now()->subDay()->format('Y-m-d H:i'),
            'duration_minutes' => 30, 'mode' => 'phone', 'status' => 'confirmed',
        ])->assertSessionHasErrors('scheduled_at');

        $appointment = Appointment::query()->sole();
        $this->actingAs($this->admin)->patch(route('admin.appointments.update', $appointment), [
            'scheduled_at' => now()->addDays(4)->setTime(11, 0)->format('Y-m-d H:i'), 'duration_minutes' => 30,
            'mode' => 'office', 'status' => 'confirmed', 'staff_user_id' => $this->admin->id,
        ])->assertSessionHasNoErrors();
        Notification::assertSentTo($studentUser, AppointmentUpdated::class);

        $this->actingAs($studentUser)->get(route('student.applications.show', $application))->assertOk()->assertSee('Confirmed');
        $this->actingAs($this->admin)->get(route('admin.appointments.index'))->assertOk()->assertSee('Ayesha');
    }

    public function test_student_requests_appointment_with_limits(): void
    {
        $studentUser = $this->user('customer', ['email' => 'student@example.com']);
        $application = $this->application($studentUser);
        $request = fn (int $days) => $this->actingAs($studentUser)->post(route('student.applications.appointments.store', $application), [
            'scheduled_at' => now()->addDays($days)->setTime(15, 0)->format('Y-m-d H:i'), 'mode' => 'video', 'student_note' => 'Course choice',
        ]);

        $request(2)->assertRedirect(route('student.applications.show', $application));
        $request(3)->assertRedirect();
        $request(4)->assertSessionHasErrors('scheduled_at');
        $this->assertSame(2, Appointment::query()->where('status', 'requested')->count());
        $this->assertSame($this->admin->id, Appointment::query()->first()->staff_user_id);

        $this->actingAs($this->user('customer'))->post(route('student.applications.appointments.store', $application), [
            'scheduled_at' => now()->addDays(5)->format('Y-m-d H:i'), 'mode' => 'video',
        ])->assertForbidden();
    }
}
