<?php

namespace Tests\Feature\Travel;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelServiceRouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_service_landing_pages_are_public_for_guests(): void
    {
        foreach (
            ['hotels.index', 'tours.index', 'visa.index'] as $routeName
        ) {
            $this->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_unverified_customer_can_browse_service_landing_pages(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('customer');

        foreach (
            ['hotels.index', 'tours.index', 'visa.index'] as $routeName
        ) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_unverified_customer_is_redirected_before_service_actions(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('customer');

        foreach (
            ['hotels.search', 'tours.search', 'visa.requirements'] as $routeName
        ) {
            $this->actingAs($user)
                ->post(route($routeName))
                ->assertRedirect(route('verification.notice'));
        }
    }
}
