<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHomepageStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_admin_renders_its_page_specific_styles(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('admin.homepage.index'));

        $response
            ->assertOk()
            ->assertSeeText('Site, Homepage & SEO')
            ->assertSee('.home-admin{display:grid;gap:22px}', false)
            ->assertSee('.home-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))', false);
    }
}
