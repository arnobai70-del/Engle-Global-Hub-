<?php

namespace Tests\Feature\Consultancy;

use App\Models\StudentEnquiry;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SingaporeStudentEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [
            'full_name' => 'Rahim Uddin',
            'email' => 'Rahim@Example.com',
            'phone' => '+8801712345678',
            'destination' => 'SG',
            'study_level' => 'bachelor',
            'highest_qualification' => 'HSC 2025',
            'preferred_intake' => 'January 2027',
            'wants_consultation' => '1',
            'lead_source' => 'facebook_instagram',
            'message' => 'Which courses suit me?',
            'consent' => '1',
            ...$overrides,
        ];
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_can_view_singapore_page_with_seo_and_honest_disclaimer(): void
    {
        $this->get(route('consultancy.singapore'))
            ->assertOk()
            ->assertSee('<title>Study in Singapore from Bangladesh | Eagle Global Hub LTD</title>', false)
            ->assertSee('<link rel="canonical" href="'.route('consultancy.singapore').'">', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('Immigration &amp; Checkpoints Authority (ICA)', false)
            ->assertSee('cannot guarantee admission or a Student&#039;s Pass', false)
            ->assertSee('action="'.route('leads.consultation').'"', false)
            ->assertSee('Bulgaria')
            ->assertSee('Cyprus');
    }

    public function test_guest_enquiry_is_stored_as_a_new_lead_with_consent(): void
    {
        $this->post(route('consultancy.enquiries.store'), $this->payload())
            ->assertRedirect(route('consultancy.singapore'))
            ->assertSessionHas('enquiry_reference');

        $enquiry = StudentEnquiry::query()->with('destinationCountry')->sole();
        $this->assertSame('rahim@example.com', $enquiry->email);
        $this->assertSame('SG', $enquiry->destinationCountry->iso2);
        $this->assertSame(StudentEnquiry::STATUS_NEW, $enquiry->status);
        $this->assertSame('facebook_instagram', $enquiry->lead_source);
        $this->assertTrue($enquiry->wants_consultation);
        $this->assertNotNull($enquiry->consented_at);
        $this->assertMatchesRegularExpression('/^EGH-SE-[A-Z0-9]{10}$/', $enquiry->reference);

        $this->followingRedirects()->post(route('consultancy.enquiries.store'), $this->payload(['email' => 'second@example.com']))
            ->assertSee('Thank you, we received your request.');
    }

    public function test_enquiry_rejects_missing_consent_bad_destination_bad_phone_and_honeypot(): void
    {
        $this->post(route('consultancy.enquiries.store'), $this->payload(['consent' => null]))
            ->assertSessionHasErrors('consent');
        $this->post(route('consultancy.enquiries.store'), $this->payload(['destination' => 'US']))
            ->assertSessionHasErrors('destination');
        $this->post(route('consultancy.enquiries.store'), $this->payload(['phone' => 'call me']))
            ->assertSessionHasErrors('phone');
        $this->post(route('consultancy.enquiries.store'), $this->payload(['website' => 'http://spam.test']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('student_enquiries', 0);
    }

    public function test_enquiry_submission_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('consultancy.enquiries.store'), $this->payload(['email' => "lead{$attempt}@example.com"]))
                ->assertRedirect();
        }

        $this->post(route('consultancy.enquiries.store'), $this->payload(['email' => 'lead6@example.com']))
            ->assertStatus(429);
        $this->assertDatabaseCount('student_enquiries', 5);
    }

    public function test_singapore_page_is_listed_in_sitemap(): void
    {
        $this->get(route('sitemap'))->assertOk()->assertSee(route('consultancy.singapore'), false);
    }

    public function test_only_staff_with_permission_can_view_and_update_enquiries(): void
    {
        $this->post(route('consultancy.enquiries.store'), $this->payload());
        $enquiry = StudentEnquiry::query()->sole();

        $this->get(route('admin.student-enquiries.index'))->assertRedirect(route('login'));
        $customer = $this->user('customer');
        $this->actingAs($customer)->get(route('admin.student-enquiries.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.student-enquiries.show', $enquiry))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.student-enquiries.update', $enquiry), ['status' => 'contacted'])->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.student-enquiries.index'))
            ->assertOk()->assertSee($enquiry->reference)->assertSee('Facebook / Instagram');
        $this->actingAs($admin)->get(route('admin.student-enquiries.show', $enquiry))
            ->assertOk()->assertSee('Which courses suit me?');

        $this->actingAs($admin)->patch(route('admin.student-enquiries.update', $enquiry), [
            'status' => 'contacted',
            'assigned_to_user_id' => $admin->id,
            'follow_up_at' => now()->addDay()->format('Y-m-d H:i'),
            'internal_notes' => 'Called, prefers evening.',
        ])->assertRedirect(route('admin.student-enquiries.show', $enquiry));

        $enquiry->refresh();
        $this->assertSame('contacted', $enquiry->status);
        $this->assertSame($admin->id, $enquiry->assigned_to_user_id);
        $this->assertNotNull($enquiry->follow_up_at);
    }

    public function test_enquiry_cannot_be_assigned_to_a_non_staff_user_or_marked_converted_manually(): void
    {
        $this->post(route('consultancy.enquiries.store'), $this->payload());
        $enquiry = StudentEnquiry::query()->sole();
        $admin = $this->user('admin');

        $this->actingAs($admin)->patch(route('admin.student-enquiries.update', $enquiry), [
            'status' => 'contacted',
            'assigned_to_user_id' => $this->user('customer')->id,
        ])->assertSessionHasErrors('assigned_to_user_id');

        $this->actingAs($admin)->patch(route('admin.student-enquiries.update', $enquiry), ['status' => 'converted'])
            ->assertSessionHasErrors('status');

        $this->assertSame(StudentEnquiry::STATUS_NEW, $enquiry->refresh()->status);
    }
}
