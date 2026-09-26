<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Screens 4-16 contract: the page shells and the readability floor.
 *
 * The remaining mockup screens already shared the site's design system, so
 * what they were missing was measured in a browser rather than guessed at.
 * Three screens opened without any <h1> at all, one opened with an <h2> before
 * its <h1> and then skipped a heading level, and a long run of labels across
 * the site, the customer workspace and the admin area rendered between 8px and
 * 10.5px.
 *
 * These assertions lock the two fixes in. The page shells are asserted from
 * the rendered response, and the type floor is asserted from the stylesheets
 * that ship, because that is where a later change would quietly undo it.
 */
final class RemainingScreensUiContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every stylesheet the site, the customer workspace, the admin area and
     * the flight screens load from public/css.
     *
     * @var list<string>
     */
    private const THEME_SHEETS = [
        'css/egh-ota.css',
        'css/egh-workspace.css',
        'css/egh-admin.css',
        'css/egh-flight.css',
        'css/egh-hotels.css',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_remaining_screens_each_open_with_exactly_one_page_heading(): void
    {
        $screens = [
            'hotels.index' => 'Find a stay for your journey',
            'hotels.rooms' => 'Choose the room and rate for your stay',
            'hotels.booking' => 'Guest details for your stay',
            'tours.index' => 'Explore tours for your destination',
        ];

        foreach ($screens as $routeName => $heading) {
            $html = $this->actingAs($this->customer())
                ->get(route($routeName))
                ->assertOk()
                ->getContent();

            $this->assertIsString($html);

            $this->assertStringContainsString(
                $heading,
                $html,
                $routeName.' lost its page heading',
            );

            $headingCount = substr_count($html, '<h1');

            $this->assertSame(
                1,
                $headingCount,
                $routeName.' renders '.$headingCount.' <h1> elements',
            );
        }
    }

    public function test_work_visa_application_page_opens_with_its_own_heading(): void
    {
        $html = $this->get(route('work-visa.apply'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);

        $this->assertStringContainsString(
            'Prepare a work visa application',
            $html,
        );

        $this->assertSame(
            1,
            substr_count($html, '<h1'),
        );
    }

    public function test_room_selection_keeps_the_property_panel_below_the_page_heading(): void
    {
        $this->app['env'] = 'local';

        $html = $this->actingAs($this->customer())
            ->get(route('hotels.rooms'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);

        /*
         * The screen now opens with its own <h1>, so the property panel is an
         * <h2> and the room names below it stay one level down instead of
         * jumping from <h1> straight to <h3>.
         */
        $this->assertStringContainsString('<h2>Sample hotel name</h2>', $html);
        $this->assertStringNotContainsString('<h1>Sample hotel name</h1>', $html);

        /*
         * Its type treatment moved to the page-scoped hotel sheet when it
         * stopped being an <h1>, so the panel still looks exactly as it did.
         */
        $stylesheet = $this->stylesheet('css/egh-hotels.css');

        $this->assertStringContainsString('.egho-hotel-hero-copy h2 {', $stylesheet);
        $this->assertStringContainsString('font-size: 26px;', $stylesheet);
        $this->assertStringContainsString('var(--egho-navy)', $stylesheet);
    }

    public function test_hotel_screens_load_their_own_page_scoped_stylesheet(): void
    {
        foreach (['hotels.index', 'hotels.rooms', 'hotels.booking'] as $routeName) {
            $this->actingAs($this->customer())
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('css/egh-hotels.css', false);
        }
    }

    public function test_no_theme_sheet_declares_text_below_the_eleven_pixel_floor(): void
    {
        foreach (self::THEME_SHEETS as $file) {
            $stylesheet = $this->stylesheet($file);

            preg_match_all('/font-size:\s*([0-9.]+)px/', $stylesheet, $matches);

            $this->assertNotEmpty(
                $matches[1],
                $file.' declares no pixel font size at all',
            );

            foreach ($matches[1] as $size) {
                $this->assertGreaterThanOrEqual(
                    11.0,
                    (float) $size,
                    $file.' declares font-size: '.$size.'px, below the 11px floor',
                );
            }
        }
    }

    public function test_customer_workspace_and_admin_area_carry_the_same_floor(): void
    {
        $workspace = $this->stylesheet('css/egh-workspace.css');
        $admin = $this->stylesheet('css/egh-admin.css');

        $this->assertStringContainsString('.dashboard-account-grid span', $workspace);
        $this->assertStringContainsString('.dashboard-account-grid strong', $workspace);
        $this->assertStringContainsString('.dashboard-verified small', $workspace);
        $this->assertStringContainsString('.dashboard-account-link', $workspace);
        $this->assertStringContainsString('.dashboard-service-card small', $workspace);
        $this->assertStringContainsString('.dashboard-quick-card small', $workspace);

        $this->assertStringContainsString('.admin-body .admin-nav-title', $admin);
        $this->assertStringContainsString('.admin-body .admin-page-eyebrow', $admin);
        $this->assertStringContainsString('.admin-body .admin-page-breadcrumb strong', $admin);
        $this->assertStringContainsString('.admin-body .settings-table th', $admin);
        $this->assertStringContainsString('.admin-body .settings-type-badge', $admin);
        $this->assertStringContainsString('.admin-body .admin-action-link', $admin);

        /*
         * Both sheets only reach their own area.
         */
        $this->assertStringContainsString('body.dashboard-body', $workspace);
        $this->assertStringContainsString('.admin-body', $admin);
    }

    public function test_footer_and_topbar_controls_keep_a_real_tap_target(): void
    {
        $stylesheet = $this->stylesheet('css/egh-ota.css');

        foreach ([
            '.egho-topbar-item',
            '.egho-section-link',
        ] as $selector) {
            $this->assertMatchesRegularExpression(
                '/'.preg_quote($selector, '/').'\s*\{[^}]*min-height:\s*32px;/s',
                $stylesheet,
                $selector.' has no 32px tap target',
            );
        }

        $this->assertMatchesRegularExpression(
            '/\.egho-footer-list a,\s*\.egho-footer-list span\s*\{[^}]*min-height:\s*32px;/s',
            $stylesheet,
            'the footer links have no 32px tap target',
        );
    }

    private function stylesheet(string $file): string
    {
        $stylesheet = file_get_contents(public_path($file));

        $this->assertIsString($stylesheet);

        return $stylesheet;
    }

    private function customer(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->assignRole('customer');

        return $user;
    }
}
