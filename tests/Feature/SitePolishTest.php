<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePolishTest extends TestCase
{
    use RefreshDatabase;

    private const HIDDEN_TOOLS = ['consultancy.malaysia.eligibility', 'consultancy.malaysia.cost-calculator', 'consultancy.malaysia.courses', 'appointments.book'];

    public function test_scroll_reveal_never_hides_content_before_or_after_the_script_runs(): void
    {
        $css = (string) file_get_contents(public_path('css/egh-v2.css'));
        $js = (string) file_get_contents(public_path('js/egh-sg-nav.js'));

        // No rule hides revealable content up front: the only opacity:0 is the animation's first frame.
        $this->assertDoesNotMatchRegularExpression('/\[data-reveal\][^{]*\{[^}]*opacity\s*:\s*0/', $css);
        $this->assertDoesNotMatchRegularExpression('/\.egh-reveal(?!-in)[^{]*\{[^}]*opacity\s*:\s*0/', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $css);
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: no-preference\)\s*\{\s*\.egh-reveal-in\s*\{\s*animation:/', $css);

        $this->assertStringContainsString("rootMargin: '0px 0px 30% 0px'", $js);
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $js);
        $this->assertStringContainsString("'animationend'", $js);
    }

    public function test_hidden_planning_tools_send_visitors_to_study_in_malaysia_and_stay_out_of_the_sitemap_and_menus(): void
    {
        $this->seed([RolePermissionSeeder::class, MenuSeeder::class]);
        $home = (string) $this->get(route('home'))->getContent();
        $sitemap = (string) $this->get(route('sitemap'))->getContent();

        foreach (self::HIDDEN_TOOLS as $tool) {
            $this->get(route($tool))
                ->assertRedirect(route('consultancy.malaysia').'#apply')
                ->assertSessionHas('api_waiting');
            $this->assertStringNotContainsString('<loc>'.route($tool).'</loc>', $sitemap, $tool);
            $this->assertStringNotContainsString('href="'.route($tool).'"', $home, $tool);
        }

        $this->followingRedirects()->get(route('consultancy.malaysia.eligibility'))
            ->assertOk()->assertSee('This tool opens soon.');
    }

    public function test_guests_opening_partner_registration_get_the_public_partner_page(): void
    {
        $this->get(route('b2b.register'))->assertRedirect(route('b2b.info').'#apply');

        $this->get(route('b2b.info'))->assertOk()
            ->assertSee('Who it is for')
            ->assertSee('What you get')
            ->assertSee('How it works')
            ->assertSee('id="apply"', false)
            ->assertSee('Register as partner');
    }

    public function test_signed_in_customers_still_get_the_registration_form(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('customer');

        $this->actingAs($user)->get(route('b2b.register'))->assertOk();
    }

    public function test_the_home_services_grid_has_a_neutral_heading_and_balanced_columns(): void
    {
        $page = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('Study abroad, visas and travel from one team', $page);
        $this->assertMatchesRegularExpression('/class="v2-services v2-services-(\d)" data-cards="(\d+)"/', $page);
        preg_match('/class="v2-services v2-services-(\d)" data-cards="(\d+)"/', $page, $grid);
        $cards = (int) $grid[2];
        $this->assertSame($cards % 4 === 0 || $cards === 7 ? '4' : '3', $grid[1]);
    }

    public function test_one_phone_number_gives_the_same_display_call_and_whatsapp_link_everywhere(): void
    {
        app(SettingService::class)->set('contact', 'phone', '01715127464');

        $this->get(route('support'))->assertOk()
            ->assertSee('+880 1715-127464')
            ->assertSee('href="tel:+8801715127464"', false)
            ->assertSee('https://wa.me/8801715127464', false)
            ->assertDontSee('tel:01715127464', false);

        $this->assertDatabaseHas('settings', ['group' => 'contact', 'key' => 'phone', 'value' => '01715127464']);
    }

    public function test_the_settings_form_stores_the_phone_in_international_format(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('super-admin');
        $payload = ['site_name' => 'Eagle Global Hub', 'hero_title' => 'Study and travel', 'seo_title' => 'Eagle Global Hub', 'seo_description' => 'Study abroad and travel.'];

        $this->actingAs($admin)->patch(route('admin.homepage.settings.update'), [...$payload, 'phone' => '12345'])
            ->assertSessionHasErrors('phone');
        $this->actingAs($admin)->patch(route('admin.homepage.settings.update'), [...$payload, 'phone' => '01715127464'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', ['group' => 'contact', 'key' => 'phone', 'value' => '+8801715127464']);
        $this->actingAs($admin)->patch(route('admin.homepage.settings.update'), [...$payload, 'phone' => '+880 1715-127464'])
            ->assertSessionHasNoErrors();
    }
}
