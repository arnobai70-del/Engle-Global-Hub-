<?php

namespace Tests\Feature;

use App\Enums\StudentApplicationStatus;
use App\Models\Agent;
use App\Models\ContactDirectoryEntry;
use App\Models\Country;
use App\Models\Destination;
use App\Models\ServiceEnquiry;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ServiceEnquiryUpdated;
use App\Services\Consultancy\StudentApplicationWorkflow;
use Database\Seeders\BangladeshDestinationSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BusinessModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
        Notification::fake();
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), ...$attributes]);
        $user->assignRole($role);

        return $user;
    }

    public function test_contact_directory_limits_and_public_display(): void
    {
        $editor = $this->user('content-editor');
        $add = fn (array $data) => $this->actingAs($editor)->post(route('admin.contact-directory.store'), ['kind' => 'phone', 'label' => 'Desk', 'sort_order' => 0, 'is_active' => '1', ...$data]);

        for ($i = 1; $i <= 10; $i++) {
            $add(['phone' => '+88017000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'is_public' => $i === 1 ? '1' : null])->assertSessionHasNoErrors();
        }
        $add(['phone' => '+8801700000099'])->assertSessionHasErrors('kind');
        $add(['phone' => '+8801700000001'])->assertSessionHasErrors();
        $add(['kind' => 'ip_24x7', 'label' => '24/7 Hotline', 'phone' => '09610000000', 'is_public' => '1'])->assertSessionHasNoErrors();
        $add(['kind' => 'ip_24x7', 'label' => 'Second', 'phone' => '09610000001'])->assertSessionHasErrors('kind');

        $this->assertSame(11, ContactDirectoryEntry::query()->count());
        $this->get(route('support'))->assertOk()->assertSee('24/7 Hotline')->assertSee('+8801700000001')->assertDontSee('+8801700000002');
    }

    public function test_b2b_partner_registers_waits_for_approval_and_refers_enquiries(): void
    {
        $partnerUser = $this->user('customer', ['email' => 'partner@agency.test']);
        $this->get(route('b2b.register'))->assertRedirect(route('b2b.info').'#apply');

        $this->actingAs($partnerUser)->post(route('b2b.register.store'), [
            'company_name' => 'Partner Agency Ltd', 'name' => 'Partner Person', 'phone' => '+8801711111111', 'consent' => '1',
        ])->assertRedirect(route('b2b.register'));
        $agent = Agent::query()->sole();
        $this->assertSame(Agent::TYPE_B2B_PARTNER, $agent->partner_type);
        $this->assertSame(Agent::STATUS_PENDING, $agent->status);
        $this->actingAs($partnerUser)->post(route('b2b.register.store'), ['company_name' => 'Again', 'name' => 'x', 'phone' => '+8801711111111', 'consent' => '1'])->assertSessionHasErrors();
        $this->actingAs($partnerUser)->get(route('agent.index'))->assertForbidden();

        $agent->update(['status' => Agent::STATUS_ACTIVE]);
        $this->actingAs($partnerUser)->get(route('agent.index'))->assertOk()->assertSee('Egabe B2B partner portal');
        $this->actingAs($partnerUser)->post(route('agent.service-referrals.store'), [
            'service' => 'export_import', 'category' => 'export', 'full_name' => 'Client One', 'email' => 'client@example.com',
            'phone' => '+8801722222222', 'travellers' => 1, 'consent' => '1',
        ])->assertRedirect(route('agent.index'));

        $enquiry = ServiceEnquiry::query()->sole();
        $this->assertSame($agent->id, $enquiry->referred_by_agent_id);
        $this->actingAs($partnerUser)->get(route('agent.index'))->assertSee($enquiry->reference)->assertSee('Export');
        $this->actingAs($this->user('admin'))->get(route('admin.service-enquiries.show', $enquiry))->assertSee('Partner Agency Ltd');
    }

    public function test_foreigner_support_and_tour_categories_with_notifications(): void
    {
        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('service-enquiries.store', 'foreigner-support'), [
            'full_name' => 'Visitor Name', 'email' => 'visitor@example.com', 'phone' => '+6591234567', 'travellers' => 1, 'consent' => '1',
        ])->assertSessionHasErrors('category');
        $this->actingAs($customer)->post(route('service-enquiries.store', 'foreigner-support'), [
            'full_name' => 'Visitor Name', 'email' => 'visitor@example.com', 'phone' => '+6591234567', 'travellers' => 1, 'consent' => '1', 'category' => 'accommodation',
        ])->assertRedirect();
        $this->actingAs($customer)->post(route('service-enquiries.store', 'tour-package'), [
            'full_name' => 'Group Lead', 'email' => 'lead@example.com', 'phone' => '+8801733333333', 'travellers' => 12, 'consent' => '1', 'category' => 'group_tour',
        ])->assertRedirect();

        $support = ServiceEnquiry::query()->where('service', 'foreigner_support')->sole();
        $this->assertSame('Accommodation', $support->categoryLabel());

        $admin = $this->user('admin');
        $crm = $this->user('crm-staff');
        $this->actingAs($admin)->patch(route('admin.service-enquiries.update', $support), ['status' => 'contacted', 'assigned_to_user_id' => $crm->id])->assertRedirect();
        Notification::assertSentTo($customer, ServiceEnquiryUpdated::class, fn (ServiceEnquiryUpdated $notification): bool => ! $notification->forStaff);
        Notification::assertSentTo($crm, ServiceEnquiryUpdated::class, fn (ServiceEnquiryUpdated $notification): bool => $notification->forStaff);
        $this->actingAs($customer)->get(route('service-enquiries.mine'))->assertSee('Group tour')->assertSee('Contacted');
    }

    public function test_business_class_uses_the_flight_enquiry_with_cabin_preselected(): void
    {
        $this->get('/enquiries/business-class')->assertRedirect(route('flights.enquiries.create', ['cabin_class' => 'business']));
        $this->actingAs($this->user('admin'))->get(route('admin.flight-enquiries.index', ['cabin_class' => 'business']))->assertOk()->assertSee('Business class');
    }

    public function test_upcoming_success_pipeline_is_internal_and_scoped(): void
    {
        $consultant = $this->user('admission-consultant');
        $student = Student::query()->create(['first_name' => 'Late', 'last_name' => 'Stage', 'email' => 'late@example.com', 'reference_code' => 'EGH-ST-PIPE000001', 'status' => 'active']);
        $workflow = app(StudentApplicationWorkflow::class);
        $application = $workflow->open($student, ['destination_country_id' => (int) Country::query()->where('iso2', 'SG')->value('id'), 'assigned_consultant_id' => $consultant->id], null);
        foreach (['eligibility_review', 'documents_pending', 'documents_verified', 'admission_applied', 'offer_received'] as $status) {
            $workflow->transition($application, StudentApplicationStatus::from($status), $consultant);
        }
        $other = $workflow->open(Student::query()->create(['first_name' => 'O', 'last_name' => 'T', 'email' => 'o@example.com', 'reference_code' => 'EGH-ST-PIPE000002', 'status' => 'active']), ['destination_country_id' => $application->destination_country_id], null);

        $this->actingAs($consultant)->get(route('admin.success-pipeline.index'))->assertOk()
            ->assertSee($application->reference)->assertSee("Student's Pass Preparation")->assertDontSee($other->reference);
        $this->actingAs($consultant)->patch(route('admin.success-pipeline.follow-up', $application), ['follow_up_at' => now()->addWeek()->toDateString()])->assertRedirect();
        $this->assertNotNull($application->refresh()->follow_up_at);
        $this->actingAs($consultant)->patch(route('admin.success-pipeline.follow-up', $other), ['follow_up_at' => now()->toDateString()])->assertForbidden();
        $this->actingAs($this->user('customer'))->get(route('admin.success-pipeline.index'))->assertForbidden();
    }

    public function test_places_you_may_like_shows_only_published_recommendations(): void
    {
        $this->seed(BangladeshDestinationSeeder::class);
        $sylhet = Destination::query()->where('slug', 'sylhet')->sole();
        $kuakata = Destination::query()->where('slug', 'kuakata')->sole();
        $coxs = Destination::query()->where('slug', 'coxs-bazar')->sole();
        Destination::query()->whereKey([$sylhet->id, $kuakata->id])->update(['is_published' => true, 'summary' => 'Verified.']);

        $this->actingAs($this->user('content-editor'))->patch(route('admin.destinations.update', $sylhet), [
            'name' => 'Sylhet', 'slug' => 'sylhet', 'country_id' => $sylhet->country_id, 'sort_order' => 1, 'summary' => 'Verified.', 'is_published' => '1',
            'recommendations_submitted' => '1', 'recommended_ids' => [$kuakata->id, $coxs->id],
        ])->assertRedirect();

        $this->get(route('explore-bangladesh.show', 'sylhet'))->assertOk()
            ->assertSee('Places You May Like')->assertSee(route('explore-bangladesh.show', 'kuakata'), false)
            ->assertDontSee(route('explore-bangladesh.show', 'coxs-bazar'), false);
        $this->assertSame(count(BangladeshDestinationSeeder::destinations()), Destination::query()->count());
    }
}
