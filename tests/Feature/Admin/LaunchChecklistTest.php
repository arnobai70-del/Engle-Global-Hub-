<?php

namespace Tests\Feature\Admin;

use App\Models\EligibilityRule;
use App\Models\HomepageBlock;
use App\Models\User;
use App\Services\Admin\LaunchChecklist;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StudyDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LaunchChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    /** @return array<string, array{label: string, status: string, detail: string, url: string|null}> */
    private function items(string $section): array
    {
        $found = collect(app(LaunchChecklist::class)->sections())->firstWhere('key', $section);

        return collect($found['items'])->keyBy('label')->all();
    }

    public function test_admins_see_the_checklist_with_statuses_and_open_links(): void
    {
        foreach (['admin', 'super-admin'] as $role) {
            $this->actingAs($this->user($role))->get(route('admin.launch-checklist'))
                ->assertOk()
                ->assertSee('Website launch checklist')
                ->assertSee('data-launch-section="explore"', false)
                ->assertSee('data-launch-section="trust"', false)
                ->assertSee('data-launch-section="tools"', false)
                ->assertSee('data-launch-section="about"', false)
                ->assertSee('data-launch-section="system"', false)
                ->assertSee('data-launch-status="red"', false)
                ->assertSee('href="'.route('admin.facts.edit').'"', false);
        }
    }

    public function test_other_roles_and_guests_cannot_open_it(): void
    {
        $this->get(route('admin.launch-checklist'))->assertRedirect(route('login'));

        foreach (['content-editor', 'crm-staff', 'seo-manager'] as $role) {
            $this->actingAs($this->user($role))->get(route('admin.launch-checklist'))->assertForbidden();
        }
    }

    public function test_log_mailer_is_red_and_says_enquiry_emails_are_not_sent(): void
    {
        config(['mail.default' => 'log']);
        $email = $this->items('system')['Enquiry emails'];
        $this->assertSame(LaunchChecklist::RED, $email['status']);
        $this->assertStringContainsString('enquiry emails are NOT being sent', $email['detail']);

        config(['mail.default' => 'smtp']);
        $this->assertSame(LaunchChecklist::GREEN, $this->items('system')['Enquiry emails']['status']);
    }

    public function test_sync_queue_and_a_missing_scheduler_heartbeat_are_reported(): void
    {
        config(['queue.default' => 'sync']);
        Cache::forget(LaunchChecklist::SCHEDULER_HEARTBEAT);
        $system = $this->items('system');
        $this->assertSame(LaunchChecklist::AMBER, $system['Queue connection']['status']);
        $this->assertArrayNotHasKey('Queue worker', $system);
        $this->assertSame(LaunchChecklist::RED, $system['Scheduler']['status']);

        config(['queue.default' => 'database']);
        Cache::forever(LaunchChecklist::SCHEDULER_HEARTBEAT, now()->getTimestamp());
        $system = $this->items('system');
        $this->assertSame(LaunchChecklist::GREEN, $system['Queue worker']['status']);
        $this->assertSame(LaunchChecklist::GREEN, $system['Scheduler']['status']);
    }

    public function test_planning_tools_say_what_they_need_and_turn_green_once_ready(): void
    {
        $tool = $this->items('tools')['Eligibility checker'];
        $this->assertSame(LaunchChecklist::AMBER, $tool['status']);
        $this->assertStringContainsString('Hidden from the public until at least one eligibility rule is active.', $tool['detail']);
        $this->assertSame(route('admin.eligibility-rules.index'), $tool['url']);

        EligibilityRule::factory()->create(['is_active' => true]);
        request()->attributes->remove('egh.page_availability');
        $this->assertSame(LaunchChecklist::GREEN, $this->items('tools')['Eligibility checker']['status']);
    }

    public function test_an_unusable_stored_phone_is_red_and_an_empty_one_is_amber(): void
    {
        $this->assertSame(LaunchChecklist::AMBER, $this->items('trust')['Office phone']['status']);

        app(SettingService::class)->set('contact', 'phone', 'call us');
        $this->assertSame(LaunchChecklist::RED, $this->items('trust')['Office phone']['status']);

        app(SettingService::class)->set('contact', 'phone', '+880 1953-626481');
        $this->assertSame(LaunchChecklist::GREEN, $this->items('trust')['Office phone']['status']);
    }

    public function test_each_study_destination_says_what_keeps_it_hidden(): void
    {
        $this->seed([CountrySeeder::class, StudyDestinationSeeder::class]);

        $japan = $this->items('study')['Study in Japan'];
        $this->assertSame(LaunchChecklist::AMBER, $japan['status']);
        $this->assertStringContainsString('Study in Japan: hidden until', $japan['detail']);
        $this->assertStringContainsString('Write the introduction', $japan['detail']);
        $this->assertStringContainsString('official source you have checked', $japan['detail']);
        $this->assertStringContainsString('/admin/study-destinations/', (string) $japan['url']);
    }

    public function test_the_news_ticker_row_follows_its_settings_and_headlines(): void
    {
        $this->assertSame(LaunchChecklist::INFO, $this->items('homepage')['News ticker']['status'], 'off is information only');

        app(SettingService::class)->set('homepage', 'news_enabled', '1', 'string', true);
        $row = $this->items('homepage')['News ticker'];
        $this->assertSame(LaunchChecklist::AMBER, $row['status']);
        $this->assertStringContainsString('Ticker enabled but no active headlines', $row['detail']);

        HomepageBlock::query()->create(['section' => NewsTicker::SECTION, 'title' => 'Headline', 'is_active' => true]);
        app(NewsTicker::class)->forget();
        $this->assertSame(LaunchChecklist::GREEN, $this->items('homepage')['News ticker']['status']);
    }

    public function test_the_admin_dashboard_links_to_the_checklist_with_counts(): void
    {
        $this->actingAs($this->user('super-admin'))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-launch-badge', false)
            ->assertSee(route('admin.launch-checklist'), false)
            ->assertSee('to fix');
    }
}
