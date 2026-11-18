<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role, string $name = 'Test User'): User
    {
        $user = User::factory()->create(['name' => $name, 'email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_customer_dashboard_uses_the_student_portal_shell(): void
    {
        $html = $this->actingAs($this->user('customer', 'Nusrat Jahan'))->get(route('dashboard'))->assertOk()
            ->assertSee('sg-portal-side', false)
            ->assertSee('Student Portal')
            ->assertSee('Welcome, Nusrat Jahan')
            ->assertSee('Application Progress')
            ->assertSee('aria-current="page"', false)
            ->getContent();

        $this->assertSame(1, substr_count((string) $html, '<h1'), 'the dashboard has exactly one main heading');
    }

    public function test_portal_pages_mark_their_own_menu_item(): void
    {
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('service-enquiries.mine'))->assertOk()->assertSee('Tourist Portal');
        $this->actingAs($customer)->get(route('student.applications.index'))->assertOk()->assertSee('Student Portal');
        $this->actingAs($customer)->get(route('notifications.index'))->assertOk()->assertSee('sg-portal-side', false);
    }

    public function test_admin_dashboard_figures_follow_each_staff_members_permissions(): void
    {
        $this->actingAs($this->user('super-admin'))->get(route('dashboard'))->assertOk()
            ->assertSee('Registered users')
            ->assertSee('Waiting for owner review')
            ->assertSee('Active applications');

        $this->actingAs($this->user('content-editor'))->get(route('dashboard'))->assertOk()
            ->assertSee('Draft content')
            ->assertDontSee('Registered users')
            ->assertDontSee('Waiting for owner review');
    }

    public function test_page_titles_and_descriptions_are_escaped_exactly_once(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('&amp;amp;', $html);
        $this->assertStringContainsString('<title>Study in Malaysia from Bangladesh | Eagle Global Hub</title>', $html);

        $terms = (string) $this->get(route('terms'))->assertOk()->getContent();
        $this->assertStringNotContainsString('&amp;amp;', $terms);
        $this->assertStringContainsString('<meta property="og:title" content="Terms &amp; Policies">', $terms);
    }

    public function test_public_layout_loads_the_design_system_without_inline_scripts(): void
    {
        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringContainsString('css/egh-sg.css?v=', $html);
        $this->assertStringContainsString('js/egh-sg-nav.js?v=', $html);
        $this->assertStringContainsString('Your Gateway to Study in Malaysia', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_full_desktop_menu_shows_from_1180px_and_motion_respects_reduced_motion(): void
    {
        $siteCss = (string) file_get_contents(public_path('css/egh-sg.css'));
        $v2Css = (string) file_get_contents(public_path('css/egh-v2.css'));
        $script = (string) file_get_contents(public_path('js/egh-sg-nav.js'));

        $this->assertStringContainsString('@media (max-width: 1179px) {', $siteCss, 'the burger menu is only for screens narrower than 1180px');
        $this->assertStringNotContainsString('@media (max-width: 1280px) {', $siteCss);
        $this->assertStringContainsString('(pointer: fine) and (min-width: 1180px)', $script, 'hover dropdowns start where the full menu starts');
        $this->assertStringContainsString('@media (min-width: 1180px) and (max-width: 1365px) {', $v2Css, 'laptop widths get compact menu spacing');
        $this->assertStringContainsString("header.classList.add('sg-nav-tight');\n        if (!overflows()) return;", $script, 'a long menu tightens before anything else');
        $this->assertStringContainsString("header.classList.add('sg-nav-collapsed');", $script, 'the burger is the last resort');
        $this->assertStringContainsString('.sg-nav-tight .sg-brand-copy small { display: none; }', $v2Css);

        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference) {', $v2Css);
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)').matches) return;", $script, 'scroll reveal is skipped for reduced motion');
        $this->assertStringContainsString('@media print { .egh-reveal { opacity: 1 !important;', $v2Css, 'printed pages never hide unrevealed blocks');
    }

    public function test_footer_logo_sits_on_a_white_tile_so_it_is_visible_on_navy(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('class="sg-brand sg-brand-light"><img class="sg-brand-logo"', $html);
        $this->assertMatchesRegularExpression('/\.sg-brand-light \.sg-brand-logo \{[^}]*background: #fff;/', (string) file_get_contents(public_path('css/egh-v2.css')));
    }
}
