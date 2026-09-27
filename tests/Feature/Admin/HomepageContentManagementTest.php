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
            'hero_title' => 'Travel the World with',
            'hero_accent' => 'Eagle Global Hub',
            'seo_title' => 'Eagle Global Hub | Travel Services',
            'seo_description' => 'Flights, hotels, tours and visa services from Eagle Global Hub.',
            'robots' => 'index,follow',
            'twitter_card' => 'summary_large_image',
        ];
        $this->actingAs($user)->patch(route('admin.homepage.settings.update'), $payload)->assertRedirect();
        $this->assertDatabaseHas('settings', ['group' => 'seo', 'key' => 'home_meta_title', 'value' => $payload['seo_title']]);
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
