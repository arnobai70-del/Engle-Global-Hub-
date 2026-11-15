<?php

namespace Tests\Feature\Consultancy;

use App\Enums\StudentApplicationStatus;
use App\Models\Country;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\StudentEnquiry;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $singaporeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        $this->admin = $this->user('admin');
        $this->singaporeId = (int) Country::query()->where('iso2', 'SG')->value('id');
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    private function enquiry(string $email = 'karim@example.com'): StudentEnquiry
    {
        return StudentEnquiry::query()->create([
            'reference' => 'EGH-SE-'.strtoupper(substr(md5($email.microtime()), 0, 10)),
            'full_name' => 'Abdul Karim Hasan',
            'email' => $email,
            'phone' => '+8801711111111',
            'destination_country_id' => $this->singaporeId,
            'lead_source' => 'referral',
            'consented_at' => now(),
        ]);
    }

    private function convert(StudentEnquiry $enquiry): StudentApplication
    {
        $this->actingAs($this->admin)->post(route('admin.student-enquiries.convert', $enquiry), [
            'destination_country_id' => $this->singaporeId,
            'course_name' => 'Diploma in Business',
            'intake' => 'January 2027',
            'assigned_consultant_id' => $this->admin->id,
        ])->assertRedirect();

        return StudentApplication::query()->latest('id')->firstOrFail();
    }

    public function test_converting_an_enquiry_creates_student_and_application_with_audit_entry(): void
    {
        $enquiry = $this->enquiry();
        $application = $this->convert($enquiry);

        $student = Student::query()->sole();
        $this->assertSame('karim@example.com', $student->email);
        $this->assertSame('Abdul Karim', $student->first_name);
        $this->assertSame('Hasan', $student->last_name);
        $this->assertSame(StudentApplicationStatus::NewEnquiry, $application->status);
        $this->assertSame($student->id, $application->student_id);
        $this->assertSame($enquiry->id, $application->student_enquiry_id);
        $this->assertSame($this->admin->id, $application->assigned_consultant_id);
        $this->assertSame(StudentEnquiry::STATUS_CONVERTED, $enquiry->refresh()->status);
        $this->assertSame($student->id, $enquiry->student_id);
        $this->assertDatabaseHas('student_application_status_changes', [
            'student_application_id' => $application->id,
            'from_status' => null,
            'to_status' => 'new_enquiry',
            'changed_by_user_id' => $this->admin->id,
        ]);
    }

    public function test_conversion_reuses_existing_student_by_email_and_cannot_run_twice(): void
    {
        $first = $this->convert($this->enquiry('same@example.com'));
        $second = $this->convert($this->enquiry('same@example.com'));

        $this->assertSame(1, Student::query()->count());
        $this->assertSame($first->student_id, $second->student_id);

        $converted = StudentEnquiry::query()->firstOrFail();
        $this->actingAs($this->admin)->post(route('admin.student-enquiries.convert', $converted), [
            'destination_country_id' => $this->singaporeId,
        ])->assertSessionHasErrors('enquiry');
        $this->assertSame(2, StudentApplication::query()->count());
    }

    public function test_conversion_links_only_a_verified_portal_account_with_matching_email(): void
    {
        $unverified = User::factory()->create(['email' => 'unverified@example.com', 'email_verified_at' => null]);
        $this->convert($this->enquiry('unverified@example.com'));
        $this->assertNull(Student::query()->where('email', 'unverified@example.com')->value('user_id'));

        $verified = $this->user('customer', ['email' => 'verified@example.com']);
        $this->convert($this->enquiry('verified@example.com'));
        $this->assertSame($verified->id, (int) Student::query()->where('email', 'verified@example.com')->value('user_id'));
        $this->assertNotSame($unverified->id, $verified->id);
    }

    public function test_allowed_transition_is_saved_with_notes_in_audit_trail(): void
    {
        $application = $this->convert($this->enquiry());

        $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $application), [
            'status' => 'eligibility_review',
            'student_note' => 'We are reviewing your results.',
            'internal_note' => 'GPA borderline, check foundation options.',
        ])->assertRedirect(route('admin.student-applications.show', $application));

        $this->assertSame(StudentApplicationStatus::EligibilityReview, $application->refresh()->status);
        $this->assertDatabaseHas('student_application_status_changes', [
            'student_application_id' => $application->id,
            'from_status' => 'new_enquiry',
            'to_status' => 'eligibility_review',
            'student_note' => 'We are reviewing your results.',
            'internal_note' => 'GPA borderline, check foundation options.',
        ]);

        $this->actingAs($this->admin)->get(route('admin.student-applications.show', $application))
            ->assertOk()->assertSee('Eligibility Review')->assertSee('GPA borderline');
    }

    public function test_disallowed_transition_is_rejected_and_not_recorded(): void
    {
        $application = $this->convert($this->enquiry());

        $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $application), [
            'status' => 'ipa_issued',
        ])->assertSessionHasErrors('status');

        $this->assertSame(StudentApplicationStatus::NewEnquiry, $application->refresh()->status);
        $this->assertSame(1, $application->statusChanges()->count());
    }

    public function test_closed_application_accepts_no_further_transitions(): void
    {
        $application = $this->convert($this->enquiry());
        $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $application), ['status' => 'withdrawn']);

        $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $application), [
            'status' => 'new_enquiry',
        ])->assertSessionHasErrors('status');

        $this->assertTrue($application->refresh()->status->isClosed());
        $this->actingAs($this->admin)->get(route('admin.student-applications.show', $application))
            ->assertOk()->assertSee('No further status changes are possible');
    }

    public function test_full_singapore_path_reaches_completed_through_admission_and_students_pass(): void
    {
        $application = $this->convert($this->enquiry());
        $path = [
            'consultation_scheduled', 'eligibility_review', 'documents_pending', 'documents_verified',
            'admission_applied', 'offer_received', 'students_pass_preparation', 'application_submitted',
            'ipa_issued', 'formalities_pending', 'completed',
        ];

        foreach ($path as $status) {
            $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $application), ['status' => $status])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(StudentApplicationStatus::Completed, $application->refresh()->status);
        $this->assertSame(count($path) + 1, $application->statusChanges()->count());
        $this->assertSame("Student's Pass", StudentApplicationStatus::IpaIssued->stage());
        $this->assertSame('Admission', StudentApplicationStatus::OfferReceived->stage());
    }

    public function test_staff_can_open_an_application_for_an_existing_student_and_edit_details(): void
    {
        $student = Student::query()->create([
            'first_name' => 'Nusrat', 'last_name' => 'Jahan', 'email' => 'nusrat@example.com',
            'reference_code' => 'EGH-ST-TEST000001', 'status' => Student::STATUS_ACTIVE,
        ]);

        $this->actingAs($this->admin)->get(route('admin.students.show', $student))->assertSee('Open Application');
        $this->actingAs($this->admin)->get(route('admin.student-applications.create', $student))->assertOk();
        $this->actingAs($this->admin)->post(route('admin.student-applications.store', $student), [
            'destination_country_id' => $this->singaporeId,
        ])->assertRedirect();

        $application = StudentApplication::query()->sole();
        $this->actingAs($this->admin)->patch(route('admin.student-applications.update', $application), [
            'destination_country_id' => $this->singaporeId,
            'course_name' => 'BSc Computer Science',
            'intake' => 'August 2027',
            'internal_notes' => 'Prefers public universities.',
        ])->assertRedirect();

        $application->refresh();
        $this->assertSame('BSc Computer Science', $application->course_name);
        $this->assertSame('Prefers public universities.', $application->internal_notes);
        $this->assertSame(StudentApplicationStatus::NewEnquiry, $application->status);

        $this->actingAs($this->admin)->get(route('admin.student-applications.index', ['status' => 'new_enquiry', 'search' => 'Nusrat']))
            ->assertOk()->assertSee($application->reference);
    }

    public function test_customer_cannot_access_or_change_applications(): void
    {
        $application = $this->convert($this->enquiry());
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('admin.student-applications.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.student-applications.show', $application))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.student-applications.transition', $application), ['status' => 'withdrawn'])->assertForbidden();
        $this->actingAs($customer)->post(route('admin.student-enquiries.convert', StudentEnquiry::query()->firstOrFail()), [])->assertForbidden();

        $this->assertSame(StudentApplicationStatus::NewEnquiry, $application->refresh()->status);
    }

    public function test_every_status_has_label_stage_and_only_closed_statuses_are_terminal(): void
    {
        foreach (StudentApplicationStatus::cases() as $status) {
            $this->assertNotSame('', $status->label());
            $this->assertNotSame('', $status->stage());
            $this->assertSame(
                in_array($status, [StudentApplicationStatus::Completed, StudentApplicationStatus::Rejected, StudentApplicationStatus::Withdrawn], true),
                $status->isClosed(),
            );
        }
    }
}
