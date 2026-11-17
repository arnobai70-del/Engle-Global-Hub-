<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StarterContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WhyUsAndAccountTypesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_why_us_page_is_linked_and_shows_built_in_reasons_until_content_is_published(): void
    {
        $this->get(route('why-us'))->assertOk()
            ->assertSee('Why choose Eagle Global Hub')
            ->assertSee('Honest advice')
            ->assertSee('What we will never do')
            ->assertSee('No false documents');

        $this->get(route('home'))->assertSee(route('why-us'), false);
        $this->get(route('why-us', ['lang' => 'bn']))->assertOk()->assertSee('lang="bn"', false);
        $this->get(route('sitemap'))->assertSee(route('why-us'), false);
    }

    public function test_starter_content_is_published_editable_and_never_recreated_after_deletion(): void
    {
        $this->seed(StarterContentSeeder::class);

        $reasons = ContentEntry::query()->where('type', ContentType::PageSection->value)->where('slug', 'why-us')->sole();
        $this->assertSame(ContentEntry::STATUS_PUBLISHED, $reasons->status);
        $this->assertSame([], $reasons->publishBlockers());
        $this->assertGreaterThanOrEqual(8, ContentEntry::query()->published(ContentType::Faq)->count());

        $reasons->update(['body' => '- Edited by the admin: This text comes from Website Content.']);
        $this->get(route('why-us'))->assertSee('Edited by the admin')->assertDontSee('Honest advice');
        $this->get(route('home'))->assertSee('How do I start?');

        $reasons->delete();
        $this->seed(StarterContentSeeder::class);
        $this->assertFalse(ContentEntry::query()->where('slug', 'why-us')->exists(), 'deleted starter content must not come back');
        $this->get(route('why-us'))->assertSee('Honest advice');
    }

    public function test_sign_up_requires_an_account_type_and_gives_every_account_the_customer_role(): void
    {
        $this->get(route('register', ['type' => 'agent']))->assertOk()
            ->assertSee('name="account_type" value="student"', false)
            ->assertSee('value="agent" checked', false);

        $this->post(route('register.store'), $this->signUp(['account_type' => '']))->assertSessionHasErrors('account_type');
        $this->post(route('register.store'), $this->signUp(['account_type' => 'admin']))->assertSessionHasErrors('account_type');
        $this->assertGuest();

        $this->post(route('register.store'), $this->signUp(['account_type' => 'student']))->assertRedirect(route('dashboard'));
        $student = User::query()->where('email', 'new@example.com')->sole();
        $this->assertSame('student', $student->account_type);
        $this->assertTrue($student->hasRole('customer'));
        $this->assertFalse($student->isBackOffice());
    }

    public function test_agents_continue_to_the_partner_application_and_get_no_agent_access_until_approved(): void
    {
        $this->post(route('register.store'), $this->signUp(['account_type' => 'agent', 'email' => 'agent@example.com']))
            ->assertRedirect(route('b2b.register'));

        $agent = User::query()->where('email', 'agent@example.com')->sole();
        $this->assertSame(['customer'], $agent->getRoleNames()->all());

        $agent->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($agent)->get(route('agent.index'))->assertForbidden();
        $this->actingAs($agent)->get(route('dashboard'))->assertOk()->assertSee('Finish your partner application');
    }

    public function test_dashboard_shows_the_next_step_for_students_and_travellers(): void
    {
        $student = User::factory()->create(['account_type' => 'student']);
        $student->assignRole('customer');
        $this->actingAs($student)->get(route('dashboard'))->assertSee('Your next step: a free consultation');

        $traveller = User::factory()->create(['account_type' => 'traveller']);
        $traveller->assignRole('customer');
        $this->actingAs($traveller)->get(route('dashboard'))->assertSee('Plan your next trip')->assertSee(route('travel-services.index'), false);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function signUp(array $overrides = []): array
    {
        return array_merge([
            'account_type' => 'student',
            'name' => 'New Person',
            'email' => 'new@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
        ], $overrides);
    }
}
