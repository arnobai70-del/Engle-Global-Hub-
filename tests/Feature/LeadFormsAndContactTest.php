<?php

namespace Tests\Feature;

use App\Enums\BusinessService;
use App\Models\ServiceEnquiry;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Notifications\NewLeadReceived;
use App\Services\Consultancy\StudentEnquiryConverter;
use App\Services\SettingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class LeadFormsAndContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(SettingService::class)->set('contact', 'email', 'team@egh.test', 'string', true);
        app(SettingService::class)->set('contact', 'phone', '01953626481', 'string', true);
        Notification::fake();
    }

    public function test_contact_page_form_is_connected_and_saves_a_study_lead_without_email(): void
    {
        $this->get(route('support'))->assertOk()
            ->assertSee('action="'.route('leads.contact').'"', false)
            ->assertDontSee('not connected yet');

        $this->from(route('support'))->post(route('leads.contact'), $this->lead(['topic' => 'study', 'message' => 'Which diploma suits me?']))
            ->assertRedirect(route('support').'#contact-form')
            ->assertSessionHas('lead_reference');

        $lead = StudentEnquiry::query()->sole();
        $this->assertNull($lead->email);
        $this->assertSame('Which diploma suits me?', $lead->message);
        $this->assertTrue($lead->wants_consultation);
        $this->assertTeamWasEmailed();

        $this->get(route('support'))->assertSee('Thank you, we received your request.')->assertSee($lead->reference);
    }

    public function test_contact_page_saves_other_topics_as_service_enquiries(): void
    {
        $this->post(route('leads.contact'), $this->lead(['topic' => 'other', 'message' => 'Do you open on Friday?', 'email' => 'Visitor@Example.com']))->assertSessionHasNoErrors();
        $this->post(route('leads.contact'), $this->lead(['topic' => 'air-ticket', 'message' => 'Dhaka to Singapore in May']))->assertSessionHasNoErrors();

        $this->assertSame([BusinessService::General, BusinessService::AirTicket], ServiceEnquiry::query()->orderBy('id')->pluck('service')->all());
        $this->assertSame('visitor@example.com', ServiceEnquiry::query()->first()->email);
        Notification::assertSentOnDemandTimes(NewLeadReceived::class, 2);
    }

    public function test_lead_forms_validate_phone_consent_topic_and_block_bots(): void
    {
        $this->post(route('leads.contact'), $this->lead(['phone' => 'call me', 'consent' => null, 'topic' => 'hacking', 'message' => '']))
            ->assertSessionHasErrors(['phone', 'consent', 'topic', 'message']);
        $this->post(route('leads.consultation'), $this->lead(['website' => 'https://spam.example']))->assertSessionHasErrors('website');

        $this->assertDatabaseCount('student_enquiries', 0);
        $this->assertDatabaseCount('service_enquiries', 0);
        Notification::assertNothingSent();
    }

    public function test_consultation_and_quote_forms_save_leads_in_the_crm(): void
    {
        $this->post(route('leads.consultation'), $this->lead(['study_level' => 'diploma', 'preferred_intake' => 'Jan 2027', 'form' => 'home']))->assertSessionHasNoErrors();
        $student = StudentEnquiry::query()->sole();
        $this->assertSame('diploma', $student->study_level);
        $this->assertSame('Jan 2027', $student->preferred_intake);

        $this->post(route('leads.quote', 'tourist-visa'), $this->lead(['route_or_destination' => 'Thailand', 'travel_month' => now()->addMonth()->format('Y-m')]))->assertSessionHasNoErrors();
        $quote = ServiceEnquiry::query()->sole();
        $this->assertSame(BusinessService::TouristVisa, $quote->service);
        $this->assertSame('Thailand', $quote->route_or_destination);
        $this->assertSame(now()->addMonth()->format('Y-m').'-01', $quote->travel_date->format('Y-m-d'));

        $this->post(route('leads.quote', 'not-a-service'), $this->lead())->assertNotFound();
        Notification::assertSentOnDemandTimes(NewLeadReceived::class, 2);
    }

    public function test_privacy_policy_page_exists_and_the_footer_links_to_it(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Privacy Policy')->assertSee('We do not sell your personal information');
        $this->get(route('home'))->assertSee('href="'.route('privacy').'"', false);
        $this->get(route('privacy', ['lang' => 'bn']))->assertOk()->assertSee('lang="bn"', false);
    }

    public function test_whatsapp_button_is_on_every_page_and_follows_the_admin_number(): void
    {
        foreach (['home', 'support', 'privacy', 'login', 'register'] as $page) {
            $this->get(route($page))->assertSee('data-whatsapp-float', false)->assertSee('https://wa.me/8801953626481', false);
        }

        app(SettingService::class)->set('facts', 'whatsapp_number', '+65 8123 4567', 'string', true);
        $this->get(route('home'))->assertSee('https://wa.me/6581234567', false);
    }

    public function test_business_facts_are_admin_editable_validated_and_hidden_when_empty(): void
    {
        $this->actingAs($this->userWithRole('content-editor'))->get(route('admin.facts.edit'))->assertForbidden();

        $admin = $this->userWithRole('super-admin');
        $this->actingAs($admin)->get(route('admin.facts.edit'))->assertOk()->assertSee('Trade licence number')->assertSee('Tourist Visa');
        $this->actingAs($admin)->patch(route('admin.facts.update'), ['map_embed_url' => 'https://evil.example/embed', 'founded_year' => 'long ago', 'google_rating' => '9'])
            ->assertSessionHasErrors(['map_embed_url', 'founded_year', 'google_rating']);

        $this->actingAs($admin)->patch(route('admin.facts.update'), ['office_hours' => 'Sat–Thu 10am–7pm', 'reply_time' => 'Within 1 working day'])->assertSessionHasNoErrors();
        auth()->logout();
        $this->get(route('support'))->assertSee('Sat–Thu 10am–7pm')->assertSee('Within 1 working day')->assertDontSee('<iframe', false);
    }

    public function test_a_lead_without_email_cannot_be_turned_into_a_student_file_until_staff_add_one(): void
    {
        $lead = StudentEnquiry::query()->create([
            'reference' => 'EGH-SE-TEST000001', 'full_name' => 'Phone Only', 'phone' => '01711111111',
            'wants_consultation' => true, 'lead_source' => 'direct', 'consented_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        app(StudentEnquiryConverter::class)->convert($lead, [], $this->userWithRole('super-admin'));
    }

    private function assertTeamWasEmailed(): void
    {
        Notification::assertSentOnDemand(NewLeadReceived::class, fn (NewLeadReceived $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'team@egh.test');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function lead(array $overrides = []): array
    {
        return array_filter(array_merge([
            'full_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'topic' => 'study',
            'message' => 'Hello',
            'consent' => '1',
        ], $overrides), fn ($value): bool => $value !== null);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
