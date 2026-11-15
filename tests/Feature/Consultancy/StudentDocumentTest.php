<?php

namespace Tests\Feature\Consultancy;

use App\Enums\StudentApplicationStatus;
use App\Models\ActivityLog;
use App\Models\Country;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\StudentDocument;
use App\Models\User;
use App\Notifications\StudentApplicationStatusChanged;
use App\Notifications\StudentDocumentReviewed;
use App\Services\ActivityRecorder;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $studentUser;

    private StudentApplication $application;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Storage::fake('student_documents');
        Notification::fake();

        $this->admin = $this->user('admin');
        $this->studentUser = $this->user('customer', ['email' => 'student@example.com']);
        $this->application = $this->applicationFor('student@example.com', $this->studentUser);
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
        $student->forceFill(['user_id' => $linkedUser?->id])->save();

        return app(StudentApplicationWorkflow::class)->open($student, [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
        ], $this->admin);
    }

    private function upload(User $user, ?UploadedFile $file = null, string $type = 'academic_transcript')
    {
        return $this->actingAs($user)->post(route('student.applications.documents.store', $this->application), [
            'document_type' => $type,
            'document' => $file ?? UploadedFile::fake()->create('HSC transcript.pdf', 120, 'application/pdf'),
        ]);
    }

    public function test_student_upload_is_stored_privately_with_random_name_and_pending_review(): void
    {
        $this->upload($this->studentUser)->assertRedirect(route('student.applications.show', $this->application));

        $document = StudentDocument::query()->sole();
        $this->assertSame(StudentDocument::STATUS_PENDING_REVIEW, $document->status);
        $this->assertSame('HSC transcript.pdf', $document->original_name);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertMatchesRegularExpression('/^\d+\/[0-9a-f-]{36}\.pdf$/', $document->stored_path);
        Storage::disk('student_documents')->assertExists($document->stored_path);
        $this->assertStringNotContainsString('public', config('filesystems.disks.student_documents.root'));
        $this->assertFalse(config('filesystems.disks.student_documents.serve'));
        $this->assertDatabaseHas('activity_logs', ['action' => 'student_document.uploaded', 'user_id' => $this->studentUser->id]);

        $this->actingAs($this->studentUser)->get(route('student.applications.show', $this->application))
            ->assertOk()->assertSee('HSC transcript.pdf')->assertSee('Pending review');
    }

    public function test_spoofed_oversized_and_duplicate_files_are_rejected(): void
    {
        $this->upload($this->studentUser, UploadedFile::fake()->create('invoice.pdf', 10, 'text/html'))
            ->assertSessionHasErrors('document');
        $this->upload($this->studentUser, UploadedFile::fake()->create('script.php', 10, 'application/x-php'))
            ->assertSessionHasErrors('document');
        $this->upload($this->studentUser, UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf'))
            ->assertSessionHasErrors('document');
        $this->upload($this->studentUser, null, 'not_a_type')->assertSessionHasErrors('document_type');

        $this->upload($this->studentUser, UploadedFile::fake()->create('one.pdf', 50, 'application/pdf'))->assertSessionHasNoErrors();
        $this->upload($this->studentUser, UploadedFile::fake()->create('same-content.pdf', 50, 'application/pdf'))
            ->assertSessionHasErrors('document');

        $this->assertSame(1, StudentDocument::query()->count());
        $this->assertCount(1, Storage::disk('student_documents')->allFiles());
    }

    public function test_other_users_and_guests_cannot_upload_or_download(): void
    {
        $this->upload($this->studentUser);
        $document = StudentDocument::query()->sole();
        $stranger = $this->user('customer', ['email' => 'stranger@example.com']);
        $this->app['auth']->forgetGuards();

        $this->post(route('student.applications.documents.store', $this->application), [])->assertRedirect(route('login'));
        $this->get(route('student.applications.documents.download', [$this->application, $document]))->assertRedirect(route('login'));
        $this->upload($stranger)->assertForbidden();
        $this->actingAs($stranger)->get(route('student.applications.documents.download', [$this->application, $document]))->assertForbidden();
        $this->actingAs($stranger)->get(route('admin.student-applications.documents.download', [$this->application, $document]))->assertForbidden();
        $this->actingAs($stranger)->delete(route('student.applications.documents.destroy', [$this->application, $document]))->assertForbidden();

        $other = $this->applicationFor('stranger@example.com', $stranger);
        $this->actingAs($stranger)->get(route('student.applications.documents.download', [$other, $document]))->assertNotFound();
    }

    public function test_owner_and_staff_can_download_with_private_headers(): void
    {
        $this->upload($this->studentUser);
        $document = StudentDocument::query()->sole();

        $response = $this->actingAs($this->studentUser)->get(route('student.applications.documents.download', [$this->application, $document]));
        $response->assertOk()->assertDownload('hsc-transcript.pdf');
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $this->actingAs($this->admin)->get(route('admin.student-applications.documents.download', [$this->application, $document]))->assertOk();
    }

    public function test_staff_verify_and_reject_with_required_reason_and_student_is_notified(): void
    {
        $this->upload($this->studentUser);
        $document = StudentDocument::query()->sole();

        $this->actingAs($this->admin)->patch(route('admin.student-applications.documents.review', [$this->application, $document]), [
            'status' => 'rejected',
        ])->assertSessionHasErrors('review_note');
        $this->assertSame(StudentDocument::STATUS_PENDING_REVIEW, $document->refresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.student-applications.documents.review', [$this->application, $document]), [
            'status' => 'rejected', 'review_note' => 'The scan is blurry, please re-upload.',
        ])->assertRedirect();
        $document->refresh();
        $this->assertSame(StudentDocument::STATUS_REJECTED, $document->status);
        $this->assertSame($this->admin->id, $document->reviewed_by_user_id);
        Notification::assertSentTo($this->studentUser, StudentDocumentReviewed::class);

        $this->actingAs($this->admin)->patch(route('admin.student-applications.documents.review', [$this->application, $document]), [
            'status' => 'verified',
        ])->assertRedirect();
        $this->assertSame(StudentDocument::STATUS_VERIFIED, $document->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'student_document.verified', 'user_id' => $this->admin->id]);

        $this->actingAs($this->studentUser)->delete(route('student.applications.documents.destroy', [$this->application, $document]))
            ->assertSessionHasErrors('document');
        Storage::disk('student_documents')->assertExists($document->stored_path);
    }

    public function test_customer_cannot_review_and_staff_can_delete_documents(): void
    {
        $this->upload($this->studentUser);
        $document = StudentDocument::query()->sole();

        $this->actingAs($this->studentUser)->patch(route('admin.student-applications.documents.review', [$this->application, $document]), [
            'status' => 'verified',
        ])->assertForbidden();

        $this->actingAs($this->admin)->delete(route('admin.student-applications.documents.destroy', [$this->application, $document]))->assertRedirect();
        $this->assertDatabaseCount('student_documents', 0);
        Storage::disk('student_documents')->assertMissing($document->stored_path);
    }

    public function test_student_can_remove_own_pending_document(): void
    {
        $this->upload($this->studentUser);
        $document = StudentDocument::query()->sole();

        $this->actingAs($this->studentUser)->delete(route('student.applications.documents.destroy', [$this->application, $document]))->assertRedirect();
        $this->assertDatabaseCount('student_documents', 0);
        Storage::disk('student_documents')->assertMissing($document->stored_path);
    }

    public function test_closed_application_rejects_uploads(): void
    {
        app(StudentApplicationWorkflow::class)->transition($this->application, StudentApplicationStatus::Withdrawn, $this->admin);

        $this->upload($this->studentUser)->assertSessionHasErrors('document');
        $this->assertDatabaseCount('student_documents', 0);
    }

    public function test_status_change_notifies_student_without_internal_note(): void
    {
        $this->actingAs($this->admin)->post(route('admin.student-applications.transition', $this->application), [
            'status' => 'eligibility_review',
            'student_note' => 'Reviewing your results.',
            'internal_note' => 'SECRET-INTERNAL',
        ]);

        Notification::assertSentTo($this->studentUser, StudentApplicationStatusChanged::class, function (StudentApplicationStatusChanged $notification): bool {
            $payload = json_encode($notification->toArray($this->studentUser)).$notification->toMail($this->studentUser)->render();

            return str_contains($payload, 'Eligibility Review')
                && str_contains($payload, 'Reviewing your results.')
                && ! str_contains($payload, 'SECRET-INTERNAL');
        });
    }

    public function test_activity_recorder_drops_sensitive_keys(): void
    {
        $log = app(ActivityRecorder::class)->record($this->admin, 'test.action', null, [
            'reference' => 'EGH-1', 'passport_number' => 'A123', 'api_token' => 'x', 'internal_note' => 'n',
        ]);

        $this->assertSame(['reference' => 'EGH-1'], $log->properties);
        $this->actingAs($this->user('super-admin'))->get(route('admin.activity-logs.index'))->assertOk()->assertSee('test.action');
        $this->actingAs($this->admin)->get(route('admin.activity-logs.index'))->assertForbidden();
        $this->assertSame(1, ActivityLog::query()->where('action', 'test.action')->count());
    }
}
