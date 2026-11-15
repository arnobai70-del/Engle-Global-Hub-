<?php

namespace Tests\Feature;

use App\Enums\StudentApplicationStatus;
use App\Models\Agent;
use App\Models\StudentApplication;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Services\Consultancy\StudentApplicationWorkflow;
use App\Services\Consultancy\StudentEnquiryConverter;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AgentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
        Notification::fake();
    }

    private function user(string $role, string $email, bool $verified = true): User
    {
        $user = User::factory()->create(['email' => $email, 'email_verified_at' => $verified ? now() : null]);
        $user->assignRole($role);

        return $user;
    }

    private function agent(string $email, string $status = Agent::STATUS_ACTIVE): Agent
    {
        return Agent::query()->create(['name' => 'Agent '.$email, 'email' => $email, 'status' => $status, 'company_name' => 'Partner Agency']);
    }

    /** @return array<string, mixed> */
    private function referral(): array
    {
        return [
            'full_name' => 'Referred Student', 'email' => 'referred@example.com', 'phone' => '+8801711111111',
            'destination' => 'SG', 'study_level' => 'diploma', 'consent' => '1',
            'message' => 'Strong maths results.',
        ];
    }

    public function test_only_verified_logins_matching_an_active_agent_can_enter(): void
    {
        $this->agent('pending@agency.test', Agent::STATUS_PENDING);
        $this->actingAs($this->user('customer', 'pending@agency.test'))->get(route('agent.index'))->assertForbidden();

        $this->agent('unverified@agency.test');
        $this->actingAs($this->user('customer', 'unverified@agency.test', false))->get(route('agent.index'))->assertRedirect();

        $this->actingAs($this->user('customer', 'nobody@example.com'))->get(route('agent.index'))->assertForbidden();

        $this->agent('active@agency.test');
        $active = $this->user('customer', 'active@agency.test');
        $this->actingAs($active)->get(route('agent.index'))->assertOk()->assertSee('Partner Agency');
        $this->actingAs($active)->get(route('dashboard'))->assertSee(route('agent.index'), false);
    }

    public function test_agent_referral_creates_tagged_lead_and_agent_tracks_status_without_internal_notes(): void
    {
        $agent = $this->agent('active@agency.test');
        $agentUser = $this->user('customer', 'active@agency.test');

        $this->actingAs($agentUser)->post(route('agent.referrals.store'), $this->referral())->assertRedirect(route('agent.index'));

        $enquiry = StudentEnquiry::query()->sole();
        $this->assertSame($agent->id, $enquiry->referred_by_agent_id);
        $this->assertSame('referral', $enquiry->lead_source);

        $admin = $this->user('admin', 'admin@example.com');
        $this->actingAs($admin)->get(route('admin.student-enquiries.show', $enquiry))->assertSee('referred by agent');

        $application = app(StudentEnquiryConverter::class)->convert($enquiry, [
            'destination_country_id' => $enquiry->destination_country_id,
        ], $admin);
        app(StudentApplicationWorkflow::class)->transition($application, StudentApplicationStatus::EligibilityReview, $admin, null, 'SECRET-INTERNAL');

        $this->actingAs($agentUser)->get(route('agent.index'))->assertOk()
            ->assertSee($enquiry->reference)->assertSee('Eligibility Review')
            ->assertDontSee('SECRET-INTERNAL')->assertDontSee('Strong maths results.');
    }

    public function test_agent_sees_only_own_referrals_and_validation_applies(): void
    {
        $this->agent('one@agency.test');
        $this->agent('two@agency.test');
        $first = $this->user('customer', 'one@agency.test');
        $second = $this->user('customer', 'two@agency.test');

        $this->actingAs($first)->post(route('agent.referrals.store'), $this->referral());
        $reference = StudentEnquiry::query()->sole()->reference;
        $this->actingAs($first)->get(route('agent.index'))->assertSee($reference);
        $this->flushSession();

        $this->actingAs($second)->get(route('agent.index'))->assertOk()->assertDontSee($reference);
        $this->actingAs($second)->post(route('agent.referrals.store'), [...$this->referral(), 'consent' => null])
            ->assertSessionHasErrors('consent');
        $this->actingAs($this->user('customer', 'customer@example.com'))->post(route('agent.referrals.store'), $this->referral())
            ->assertForbidden();

        $this->assertSame(1, StudentEnquiry::query()->count());
        $this->assertSame(0, StudentApplication::query()->count());
    }
}
