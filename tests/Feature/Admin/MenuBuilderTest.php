<?php

namespace Tests\Feature\Admin;

use App\Models\MenuItem;
use App\Models\User;
use App\Services\Feature\FeatureManager;
use App\Services\SettingService;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MenuBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_only_users_with_menu_permission_can_open_the_menu_builder(): void
    {
        $this->actingAs($this->userWithRole('admin'))->get(route('admin.menus.index'))->assertOk()->assertSee('Menus &amp; Branding', false);
        $this->actingAs($this->userWithRole('content-editor'))->get(route('admin.menus.index'))->assertForbidden();
        $this->actingAs($this->userWithRole('customer'))->get(route('admin.menus.index'))->assertForbidden();
        $this->actingAs($this->userWithRole('content-editor'))->post(route('admin.menus.store'), $this->payload())->assertForbidden();

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_admin_can_create_edit_reorder_hide_and_delete_menu_items(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['label_en' => 'Visa', 'label_bn' => 'ভিসা', 'route_target' => 'route:visa.index', 'sort_order' => 10]))
            ->assertRedirect(route('admin.menus.index', ['location' => 'header']));
        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['label_en' => 'Hotels', 'route_target' => 'route:hotels.index', 'sort_order' => 20]))->assertSessionHasNoErrors();

        $this->actingAs($admin)->get(route('admin.menus.create', ['location' => 'header']))->assertOk()->assertSee('name="route_target"', false)->assertSee('Heading without a link');
        $this->actingAs($admin)->get(route('admin.menus.create', ['location' => 'unknown']))->assertNotFound();

        $visa = MenuItem::query()->where('label_en', 'Visa')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.menus.edit', $visa))->assertOk()->assertSee('value="ভিসা"', false)->assertSee('<option value="route:visa.index" selected', false);
        $hotels = MenuItem::query()->where('label_en', 'Hotels')->firstOrFail();
        $this->assertSame('route:visa.index', $visa->target);
        $this->assertSame('ভিসা', $visa->label_bn);

        $this->actingAs($admin)->patch(route('admin.menus.update', $visa), $this->payload(['label_en' => 'Visa Services', 'route_target' => 'route:visa.index', 'sort_order' => 10]))->assertSessionHasNoErrors();
        $this->assertSame('Visa Services', $visa->refresh()->label_en);

        $this->actingAs($admin)->patch(route('admin.menus.move', $hotels), ['direction' => 'up']);
        $this->assertLessThan($visa->refresh()->sort_order, $hotels->refresh()->sort_order);

        $this->actingAs($admin)->patch(route('admin.menus.toggle', $hotels));
        $this->assertFalse($hotels->refresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.menus.destroy', $visa))->assertRedirect();
        $this->assertModelMissing($visa);
    }

    public function test_duplicate_labels_and_duplicate_links_at_the_same_level_are_rejected(): void
    {
        $admin = $this->userWithRole('admin');
        $this->menuItem(['label_en' => 'Hotels', 'target' => 'route:hotels.index']);

        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['label_en' => 'hotels', 'route_target' => 'route:tours.index']))
            ->assertSessionHasErrors('label_en');
        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['label_en' => 'Stays', 'route_target' => 'route:hotels.index']))
            ->assertSessionHasErrors('route_target');

        $this->assertDatabaseCount('menu_items', 1);
    }

    public function test_unknown_internal_pages_and_non_https_external_links_are_rejected(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['route_target' => 'route:admin.users.index']))->assertSessionHasErrors('route_target');
        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['route_target' => 'route:services.show|not-a-service']))->assertSessionHasErrors('route_target');
        foreach (['http://example.com', 'javascript:alert(1)', 'https://exa mple.com', 'not a url'] as $url) {
            $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['link_type' => 'url', 'external_url' => $url]))->assertSessionHasErrors('external_url');
        }
        $this->assertDatabaseCount('menu_items', 0);

        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['link_type' => 'url', 'external_url' => 'https://example.com/page', 'open_in_new_tab' => '1']))->assertSessionHasNoErrors();
        $this->assertSame('url:https://example.com/page', MenuItem::query()->sole()->target);
    }

    public function test_header_renders_managed_items_with_role_visibility_and_hides_inactive_items(): void
    {
        $study = $this->menuItem(['label_en' => 'Managed Study', 'target' => null]);
        $this->menuItem(['label_en' => 'Managed Guides', 'parent_id' => $study->id, 'target' => 'route:visa.index']);
        $this->menuItem(['label_en' => 'Managed Hidden', 'target' => 'route:tours.index', 'is_active' => false]);
        $this->menuItem(['label_en' => 'Managed Agents', 'target' => 'route:dashboard', 'visibility' => 'roles', 'roles' => ['visa-officer']]);
        $this->menuItem(['label_en' => 'Managed Partner', 'target' => 'url:https://partner.example.com', 'open_in_new_tab' => true]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Managed Study')
            ->assertSee('Managed Guides')
            ->assertSee(route('visa.index'), false)
            ->assertSee('https://partner.example.com', false)->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('Managed Hidden')
            ->assertDontSee('Managed Agents');

        $this->actingAs($this->userWithRole('visa-officer'))->get(route('home'))->assertSee('Managed Agents');
        $this->actingAs($this->userWithRole('customer'))->get(route('home'))->assertDontSee('Managed Agents');
    }

    public function test_items_for_switched_off_features_and_broken_targets_are_not_rendered(): void
    {
        $this->menuItem(['label_en' => 'Managed Hotels', 'target' => 'route:hotels.index']);
        $this->menuItem(['label_en' => 'Managed Broken', 'target' => 'route:services.show|removed-service']);
        app(FeatureManager::class)->update('hotels', ['enabled' => false, 'public_visible' => true, 'authenticated_visible' => true, 'admin_visible' => true, 'message' => null]);

        $this->get(route('home'))->assertOk()->assertDontSee('Managed Hotels')->assertDontSee('Managed Broken');
    }

    public function test_built_in_navigation_is_used_until_menus_exist_and_seeded_menus_render_in_bengali(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('<summary>Student Visa</summary>', false)->assertSee('Free Consultation');

        $this->seed(MenuSeeder::class);
        $this->seed(MenuSeeder::class);
        $this->assertSame(1, MenuItem::query()->where('key', 'contact')->count());

        $this->get(route('home', ['lang' => 'bn']))
            ->assertOk()
            ->assertSee(MenuItem::query()->where('key', 'travel')->value('label_bn'))
            ->assertSee('<summary>স্টুডেন্ট ভিসা</summary>', false);
    }

    public function test_items_without_a_bengali_label_use_the_site_translation(): void
    {
        $this->menuItem(['label_en' => 'Visa services', 'label_bn' => null, 'target' => 'route:visa.index']);

        $this->get(route('home', ['lang' => 'bn']))->assertOk()->assertSee('ভিসা সেবা');
    }

    public function test_homepage_tiles_and_header_button_follow_the_managed_menus(): void
    {
        $this->menuItem(['location' => 'home_tiles', 'label_en' => 'Managed Tile', 'target' => 'route:tours.index', 'icon' => 'mountain']);
        $this->menuItem(['location' => 'header_cta', 'label_en' => 'Book a Call', 'target' => 'route:support']);

        $this->get(route('home'))->assertOk()->assertSee('Managed Tile')->assertSee('Book a Call')->assertSee('<a href="'.route('support').'" class="sg-btn sg-btn-red sg-btn-sm"', false)->assertDontSee('<a href="'.route('consultancy.singapore').'#consultation" class="sg-btn sg-btn-red sg-btn-sm"', false);
    }

    public function test_homepage_tiles_require_an_icon_and_headings_must_be_top_level(): void
    {
        $admin = $this->userWithRole('admin');
        $parent = $this->menuItem(['label_en' => 'Parent', 'target' => null]);

        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['location' => 'home_tiles']))->assertSessionHasErrors('icon');
        $this->actingAs($admin)->post(route('admin.menus.store'), $this->payload(['link_type' => 'none', 'parent_id' => $parent->id]))->assertSessionHasErrors('link_type');
    }

    public function test_header_tagline_is_editable_in_english_and_bengali(): void
    {
        $this->get(route('home'))->assertSee('Your Gateway to Study in Malaysia');

        $this->actingAs($this->userWithRole('admin'))->patch(route('admin.menus.branding'), ['header_tagline' => '<b>Study and travel</b>', 'header_tagline_bn' => 'পড়াশোনা ও ভ্রমণ'])->assertSessionHasNoErrors();
        auth()->logout();

        $this->get(route('home'))->assertSee('Study and travel')->assertDontSee('<b>Study and travel</b>', false);
        $this->get(route('home', ['lang' => 'bn']))->assertSee('পড়াশোনা ও ভ্রমণ');
    }

    public function test_original_logo_is_the_default_and_an_uploaded_logo_setting_is_honoured(): void
    {
        $this->get(route('home'))->assertSee(asset('images/eagle-global-hub-logo.png'), false);
        $this->get(route('login'))->assertSee(asset('images/eagle-global-hub-logo.png'), false);

        app(SettingService::class)->set('general', 'logo_path', 'homepage/missing-logo.png', 'string', true);
        $this->get(route('home'))->assertSee(asset('images/eagle-global-hub-logo.png'), false);

        Storage::fake('public');
        Storage::disk('public')->put('homepage/custom-logo.png', 'logo');
        app(SettingService::class)->set('general', 'logo_path', 'homepage/custom-logo.png', 'string', true);

        $this->get(route('home'))->assertSee('custom-logo.png', false);
        $this->get(route('login'))->assertSee('custom-logo.png', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'location' => 'header',
            'label_en' => 'Tours',
            'link_type' => 'route',
            'route_target' => 'route:tours.index',
            'visibility' => 'all',
            'is_active' => '1',
            'sort_order' => 10,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function menuItem(array $attributes): MenuItem
    {
        return MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true, 'sort_order' => 10], $attributes));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
