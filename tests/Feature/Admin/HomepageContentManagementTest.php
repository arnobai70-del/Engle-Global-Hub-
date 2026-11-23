<?php

namespace Tests\Feature\Admin;

use App\Models\HomepageBlock;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    public function test_super_admin_can_update_homepage_settings(): void
    {
        $user = $this->superAdmin();
        $payload = [
            'site_name' => 'Eagle Global Hub LTD',
            'site_tagline' => 'Travel & Visa Services',
            'news_enabled' => '1',
            'news_label' => 'Latest News',
            'news_text' => 'Travel service updates appear here.',
            'news_url' => 'https://example.com/news',
            'news_link_label' => 'Read More',
            'hero_title' => 'Travel the World with',
            'hero_accent' => 'Eagle Global Hub',
            'seo_title' => 'Eagle Global Hub | Travel Services',
            'seo_description' => 'Flights, hotels, tours and visa services from Eagle Global Hub.',
            'robots' => 'index,follow',
            'twitter_card' => 'summary_large_image',
        ];
        $this->actingAs($user)->patch(route('admin.homepage.settings.update'), $payload)->assertRedirect();
        $this->assertDatabaseHas('settings', ['group' => 'seo', 'key' => 'home_meta_title', 'value' => $payload['seo_title']]);
        // The news ticker moved to Admin › News ticker: this form never changes its settings.
        $this->assertDatabaseMissing('settings', ['group' => 'homepage', 'key' => 'news_text', 'value' => $payload['news_text']]);
        $this->assertDatabaseMissing('settings', ['group' => 'homepage', 'key' => 'news_enabled']);
    }

    public function test_renaming_the_site_saves_with_the_seeded_local_phone_and_shows_the_new_name_everywhere(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)->from(route('admin.homepage.index'))->patch(route('admin.homepage.settings.update'), [
            'site_name' => 'Renamed Company LTD',
            'phone' => '01953626481',
            'hero_title' => 'Study in Malaysia',
            'seo_title' => 'Study in Malaysia from Bangladesh',
            'seo_description' => 'Malaysia student consultancy.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.homepage.index'));

        $this->assertDatabaseHas('settings', ['group' => 'general', 'key' => 'site_name', 'value' => 'Renamed Company LTD']);
        $this->assertDatabaseHas('settings', ['group' => 'contact', 'key' => 'phone', 'value' => '+8801953626481']);

        $this->get(route('home'))->assertSee('Renamed Company');
        $this->get(route('admin.homepage.index'))->assertSee('Homepage Content | Renamed Company LTD', false)->assertSee('<strong>Renamed Company LTD</strong>', false);

        auth()->logout();
        $this->get(route('login'))->assertSee('<title>Login | Renamed Company LTD</title>', false)->assertDontSee('Eagle Global Hub');
        $this->get(route('login', ['lang' => 'bn']))->assertSee('Renamed Company LTD')->assertDontSee('ঈগল গ্লোবাল হাব');
    }

    public function test_admin_without_settings_manage_sees_a_read_only_page_instead_of_a_save_that_fails(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('admin.homepage.index'))
            ->assertOk()
            ->assertSee('You can view this page but not change it')
            ->assertSee('<fieldset class="home-lock" disabled', false)
            ->assertDontSee('+ Add new content block');

        $this->actingAs($this->superAdmin())->get(route('admin.homepage.index'))
            ->assertDontSee('You can view this page but not change it')
            ->assertDontSee('<fieldset class="home-lock" disabled', false)
            ->assertSee('Save Settings');
    }

    public function test_bengali_login_keeps_the_translated_default_name(): void
    {
        $this->get(route('login', ['lang' => 'bn']))->assertSee('ঈগল গ্লোবাল হাব লিমিটেড');
    }

    public function test_admin_can_control_homepage_section_visibility(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user)->patch(route('admin.homepage.settings.update'), [
            'site_name' => 'Eagle Global Hub LTD',
            'hero_title' => 'Travel the World with',
            'seo_title' => 'Eagle Global Hub | Travel Services',
            'seo_description' => 'Flights, hotels, tours and visa services.',
            'show_promotions' => '0',
            'show_services' => '0',
            'show_destinations' => '1',
            'destination_autoplay_interval' => 4200,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', ['group' => 'homepage', 'key' => 'show_promotions', 'value' => '0']);
        $this->assertDatabaseHas('settings', ['group' => 'homepage', 'key' => 'destination_autoplay_interval', 'value' => '4200']);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Everything you need to study abroad', $html);
        $this->assertStringContainsString('From first call to landing in Malaysia', $html);
    }

    public function test_super_admin_can_create_and_hide_repeatable_homepage_content(): void
    {
        $user = $this->superAdmin();
        $this->actingAs($user)->post(route('admin.homepage.blocks.store'), [
            'section' => 'destination', 'key' => 'tokyo', 'title' => 'Tokyo', 'subtitle' => 'Japan',
            'image_url' => 'https://example.com/tokyo.webp', 'image_alt' => 'Tokyo skyline',
            'meta' => json_encode(['tag' => 'Japan']), 'sort_order' => 90, 'is_active' => 1,
        ])->assertRedirect();
        $block = HomepageBlock::query()->where('key', 'tokyo')->firstOrFail();
        $this->assertTrue($block->is_active);
        $this->actingAs($user)->patch(route('admin.homepage.blocks.update', $block), [
            'section' => 'destination', 'key' => 'tokyo', 'title' => 'Tokyo', 'subtitle' => 'Japan', 'sort_order' => 90,
        ])->assertRedirect();
        $this->assertFalse($block->fresh()->is_active);
    }

    public function test_regular_customer_cannot_access_homepage_admin(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('customer');
        $this->actingAs($user)->get(route('admin.homepage.index'))->assertForbidden();
    }
}
