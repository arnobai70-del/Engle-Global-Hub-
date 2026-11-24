<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Services\SettingService;
use Database\Seeders\HomepageSettingsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\StarterContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class MalaysiaFirstSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_title_tagline_hero_and_steps_lead_with_malaysia(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('<title>Study in Malaysia from Bangladesh | Eagle Global Hub</title>', $html);
        $this->assertStringContainsString('Your Gateway to Study in Malaysia', $html);
        $this->assertStringContainsString('Study in Malaysia, with a team that walks every step with you.', $html);
        $this->assertStringNotContainsString('Malaysia student consultancy from Bangladesh', $html);
        $this->assertStringContainsString('From first call to landing in Malaysia', $html);
        $this->assertStringContainsString('14 working days (EMGS charter)', $html);
        $this->assertStringContainsString('name="destination" value="MY"', $html);
        $this->assertLessThan(strpos($html, '<h3>Student Pass (EMGS)</h3>'), strpos($html, '<h3>Study in Malaysia</h3>'), 'Malaysia leads the homepage services');
        $this->assertStringNotContainsString('<h3>Study in Singapore</h3>', $html, 'Singapore stays in admin and its own route, not the default homepage cards');
    }

    public function test_header_menu_follows_the_requested_order_and_points_the_cta_to_malaysia(): void
    {
        $this->seed(MenuSeeder::class);

        $this->assertSame(
            ['Student Visa', 'Visa & Travel', 'How it works', 'About', 'Contact'],
            MenuItem::query()->where('location', 'header')->whereNull('parent_id')->orderBy('sort_order')->orderBy('id')->pluck('label_en')->all(),
        );

        $html = (string) $this->get(route('home'))->getContent();
        $this->assertStringContainsString('<summary>Student Visa</summary>', $html, 'one Student Visa item lists every study destination');
        $this->assertStringContainsString('href="'.route('consultancy.malaysia').'#apply" class="sg-btn sg-btn-red sg-btn-sm"', $html);
        $this->assertStringNotContainsString('<a href="'.route('success-stories.index').'" class="sg-nav-link"', $html);
    }

    public function test_built_in_menu_and_footer_also_lead_with_malaysia(): void
    {
        $html = (string) $this->get(route('about'))->getContent();

        $this->assertStringContainsString('<summary>Student Visa</summary>', $html);
        $this->assertStringNotContainsString('<summary>Study in Singapore</summary>', $html);
        $this->assertStringContainsString('data-footer-cta="neutral"', $html, 'About offers both study and travel');
        $footer = substr($html, (int) strpos($html, '<footer'));
        $this->assertLessThan(strpos($footer, '>Study in Singapore<'), strpos($footer, '>Study in Malaysia<'));
    }

    public function test_homepage_costs_are_in_myr_from_business_facts(): void
    {
        app(SettingService::class)->set('facts', 'my_cost_tuition', '25,000', 'string', true);
        app(SettingService::class)->set('facts', 'my_cost_total', '40,000', 'string', true);

        $this->get(route('home'))->assertSee('MYR 25,000')->assertSee('MYR 40,000')->assertDontSee('SGD ');
    }

    public function test_malaysia_faqs_come_first_and_existing_installs_only_get_the_new_entries(): void
    {
        $this->seed(StarterContentSeeder::class);
        $this->get(route('home'))->assertSeeInOrder(['How long does the Malaysia Student Pass take?', 'How do I start?']);

        // An install that already had version 1 receives only the version 2 entries.
        DB::table('content_entries')->delete();
        app(SettingService::class)->set('content', 'starter_content_version', '1');
        $this->seed(StarterContentSeeder::class);
        $this->assertSame(['malaysia-after-arrival', 'malaysia-student-pass-time'], DB::table('content_entries')->orderBy('slug')->pluck('slug')->all());
    }

    public function test_title_default_is_updated_only_when_nobody_changed_it(): void
    {
        DB::table('settings')->insert(['group' => 'seo', 'key' => 'home_meta_title', 'value' => 'Study in Singapore from Bangladesh | Eagle Global Hub', 'type' => 'string', 'is_public' => true, 'created_at' => now(), 'updated_at' => now()]);
        $this->seed(HomepageSettingsSeeder::class);
        $this->assertSame('Study in Malaysia from Bangladesh | Eagle Global Hub', DB::table('settings')->where('group', 'seo')->where('key', 'home_meta_title')->value('value'));

        DB::table('settings')->where('group', 'seo')->where('key', 'home_meta_title')->update(['value' => 'Our own title']);
        $this->seed(HomepageSettingsSeeder::class);
        $this->assertSame('Our own title', DB::table('settings')->where('group', 'seo')->where('key', 'home_meta_title')->value('value'));
    }
}
