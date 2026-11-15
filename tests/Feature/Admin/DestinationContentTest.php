<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DestinationContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_from_destination_management(): void
    {
        $this->get(route('admin.destinations.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_destination_management(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('admin.destinations.index'))
            ->assertForbidden();
    }

    public function test_admin_role_without_master_data_permission_is_forbidden(): void
    {
        $admin = $this->userWithRole('admin');

        // Permissions are granted through the role, so the role permission is
        // what has to be removed to exercise the permission middleware.
        Role::findByName('admin')->revokePermissionTo('master-data.view');
        $admin->refresh();

        $this->actingAs($admin)
            ->get(route('admin.destinations.index'))
            ->assertForbidden();
    }

    public function test_admin_sees_real_destination_catalogue_without_sample_data(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertSee('Manage Destinations')
            ->assertSee('Add Destination')
            ->assertSee('No destinations yet.')
            ->assertDontSee('Sample destination one');
    }

    public function test_super_admin_sees_destination_navigation_entry(): void
    {
        $this->actingAs($this->userWithRole('super-admin'))
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(
                'href="'.route('admin.destinations.index').'"',
                false,
            );
    }

    public function test_customer_never_sees_destination_navigation_entry(): void
    {
        $customer = $this->userWithRole('customer');

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(
                'href="'.route('admin.destinations.index').'"',
                false,
            );
    }

    public function test_local_environment_shows_no_sample_rows(): void
    {
        $this->app['env'] = 'local';

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('admin.destinations.index'))
            ->assertOk()
            ->assertDontSee('Layout preview')
            ->assertDontSee('Sample destination one');
    }

    public function test_destination_writes_require_master_data_or_content_manage_permission(): void
    {
        $admin = $this->userWithRole('admin');
        Role::findByName('admin')->revokePermissionTo(['master-data.manage', 'content.manage']);
        $admin->refresh();

        $this->actingAs($admin)
            ->post(route('admin.destinations.store'), ['name' => 'Test'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.destinations.index'))
            ->assertStatus(405);

        $this->assertDatabaseCount('destinations', 0);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        return $user;
    }
}
