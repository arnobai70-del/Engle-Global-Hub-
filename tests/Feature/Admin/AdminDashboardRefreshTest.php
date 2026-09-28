<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminDashboardRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_the_refreshed_permission_aware_control_center(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Eagle Global Hub Control Center')
            ->assertSee('Homepage Content')
            ->assertSee('Settings')
            ->assertSee('Feature Control')
            ->assertSee('Master Data')
            ->assertSee('Bookings')
            ->assertSee('Users')
            ->assertSee('Roles &amp; Permissions', false)
            ->assertSee('Reports')
            ->assertSee('System Logs')
            ->assertSee('Live dashboard totals are intentionally not shown yet')
            ->assertSee(route('admin.homepage.index'), false)
            ->assertSee(route('admin.settings.manage'), false)
            ->assertSee(route('admin.features.index'), false);
    }

    public function test_customer_dashboard_stays_separate_from_admin_controls(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('customer');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('Continue your journey')
            ->assertSee('Account snapshot')
            ->assertDontSee('Eagle Global Hub Control Center')
            ->assertDontSee('Feature Control');
    }

    public function test_dashboard_refresh_keeps_the_expected_admin_routes_available(): void
    {
        foreach ([
            'admin.homepage.index',
            'admin.settings.manage',
            'admin.features.index',
            'admin.master-data.manage',
            'admin.categories.manage',
            'admin.currencies.manage',
            'admin.languages.manage',
            'admin.destinations.index',
            'admin.bookings.index',
            'admin.users.index',
            'admin.roles.index',
            'admin.reports.index',
            'admin.system-logs.index',
            'admin.agents.index',
            'admin.affiliates.index',
            'admin.students.index',
            'admin.institutions.index',
        ] as $routeName) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($routeName),
                "Expected admin route [{$routeName}] to remain registered."
            );
        }
    }
}
