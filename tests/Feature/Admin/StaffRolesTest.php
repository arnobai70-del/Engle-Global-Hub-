<?php

namespace Tests\Feature\Admin;

use App\Models\Country;
use App\Models\Student;
use App\Models\StudentApplication;
use App\Models\StudentDocument;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class]);
        Notification::fake();
        Storage::fake('student_documents');
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function application(?User $consultant, string $email): StudentApplication
    {
        $student = Student::query()->create([
            'first_name' => 'Student', 'last_name' => $email, 'email' => $email,
            'reference_code' => 'EGH-ST-'.strtoupper(substr(md5($email), 0, 10)), 'status' => Student::STATUS_ACTIVE,
        ]);

        return app(StudentApplicationWorkflow::class)->open($student, [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
            'assigned_consultant_id' => $consultant?->id,
        ], null);
    }

    public function test_all_staff_roles_exist_with_dashboard_access(): void
    {
        foreach (['admission-consultant', 'visa-officer', 'crm-staff', 'content-editor', 'seo-manager'] as $role) {
            $user = $this->user($role);
            $this->assertTrue($user->isBackOffice(), $role);
            $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Control Center');
        }
        $this->assertFalse($this->user('customer')->isBackOffice());
    }

    public function test_consultant_sees_only_assigned_applications_and_documents(): void
    {
        $consultant = $this->user('admission-consultant');
        $mine = $this->application($consultant, 'mine@example.com');
        $other = $this->application($this->user('admission-consultant'), 'other@example.com');

        $this->actingAs($consultant)->get(route('admin.student-applications.index'))->assertOk()
            ->assertSee($mine->reference)->assertDontSee($other->reference);
        $this->actingAs($consultant)->get(route('admin.student-applications.show', $mine))->assertOk();
        $this->actingAs($consultant)->get(route('admin.student-applications.show', $other))->assertForbidden();
        $this->actingAs($consultant)->post(route('admin.student-applications.transition', $other), ['status' => 'withdrawn'])->assertForbidden();
        $this->actingAs($consultant)->post(route('admin.student-applications.messages.store', $other), ['body' => 'hello'])->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.student-applications.documents.store', $other), [
            'document_type' => 'academic_transcript', 'document' => UploadedFile::fake()->create('t.pdf', 20, 'application/pdf'),
        ]);
        $document = StudentDocument::query()->sole();
        $this->actingAs($consultant)->get(route('admin.student-applications.documents.download', [$other, $document]))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.student-applications.documents.download', [$other, $document]))->assertOk();
    }

    public function test_consultant_who_converts_an_enquiry_is_assigned_to_it(): void
    {
        $consultant = $this->user('admission-consultant');
        $enquiry = StudentEnquiry::query()->create([
            'reference' => 'EGH-SE-STAFF00001', 'full_name' => 'Lead Person', 'email' => 'lead@example.com', 'phone' => '+8801700000000',
            'lead_source' => 'direct', 'consented_at' => now(),
        ]);

        $this->actingAs($consultant)->post(route('admin.student-enquiries.convert', $enquiry), [
            'destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'),
        ])->assertRedirect();

        $application = StudentApplication::query()->sole();
        $this->assertSame($consultant->id, $application->assigned_consultant_id);
        $this->actingAs($consultant)->get(route('admin.student-applications.show', $application))->assertOk();
    }

    public function test_visa_officer_verifies_documents_only_on_assigned_files(): void
    {
        $officer = $this->user('visa-officer');
        $assigned = $this->application($officer, 'visa@example.com');
        $admin = $this->user('admin');
        $this->actingAs($admin)->post(route('admin.student-applications.documents.store', $assigned), [
            'document_type' => 'passport_bio_page', 'document' => UploadedFile::fake()->create('p.pdf', 20, 'application/pdf'),
        ]);
        $document = StudentDocument::query()->sole();

        $this->actingAs($officer)->patch(route('admin.student-applications.documents.review', [$assigned, $document]), ['status' => 'verified'])
            ->assertRedirect();
        $this->assertSame('verified', $document->refresh()->status);

        $unassigned = $this->application(null, 'nobody@example.com');
        $this->actingAs($officer)->get(route('admin.student-applications.show', $unassigned))->assertForbidden();
        $this->actingAs($officer)->get(route('admin.student-enquiries.index'))->assertForbidden();
    }

    public function test_crm_content_and_seo_roles_stay_in_their_areas(): void
    {
        $crm = $this->user('crm-staff');
        $this->actingAs($crm)->get(route('admin.student-enquiries.index'))->assertOk();
        $this->actingAs($crm)->get(route('admin.service-enquiries.index'))->assertOk();
        $this->actingAs($crm)->get(route('admin.student-applications.index'))->assertForbidden();
        $this->actingAs($crm)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($crm)->get(route('admin.team-members.index'))->assertForbidden();

        $editor = $this->user('content-editor');
        $this->actingAs($editor)->get(route('admin.team-members.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.destinations.create'))->assertOk();
        $this->actingAs($editor)->get(route('admin.media.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.student-enquiries.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.seo.index'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();

        $seo = $this->user('seo-manager');
        $this->actingAs($seo)->get(route('admin.seo.index'))->assertOk();
        $this->actingAs($seo)->post(route('admin.team-members.store'), ['name' => 'x', 'role_title' => 'y', 'sort_order' => 1])->assertForbidden();
        $this->actingAs($seo)->get(route('admin.student-applications.index'))->assertForbidden();
        $this->actingAs($seo)->get(route('dashboard'))->assertDontSee(route('admin.flight-enquiries.index'), false);
    }
}
