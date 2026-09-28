<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_settings(): void
    {
        $user = $this->createUserWithRole('customer');

        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_settings(): void
    {
        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)
            ->getJson(route('admin.settings.index'))
            ->assertOk()
            ->assertJsonCount(9, 'data')
            ->assertJsonFragment([
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Eagle Global Hub LTD',
                'type' => 'string',
                'is_public' => true,
            ])
            ->assertJsonFragment([
                'group' => 'analytics',
                'key' => 'meta_pixel_enabled',
                'value' => false,
                'type' => 'boolean',
                'is_public' => true,
            ]);
    }

    public function test_a_browser_visit_to_the_settings_url_opens_the_settings_screen(): void
    {
        $user = $this->createUserWithRole('admin');

        /*
         * The bare settings URL is the JSON listing's address, so an admin
         * following a link to it used to get a page of raw JSON with no
         * navigation at all. A request that is not asking for JSON now lands
         * on the settings screen, while the JSON contract above is unchanged.
         */
        $this->actingAs($user)
            ->get(route('admin.settings.index'))
            ->assertRedirect(route('admin.settings.manage'));
    }

    public function test_admin_cannot_update_settings(): void
    {
        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'general',
                    'key' => 'site_name',
                ]),
                [
                    'value' => 'Changed By Admin',
                    'type' => 'string',
                    'is_public' => true,
                ]
            )
            ->assertForbidden();

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'site_name',
            'value' => 'Eagle Global Hub LTD',
        ]);
    }

    public function test_super_admin_can_view_one_setting(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->getJson(
                route('admin.settings.show', [
                    'group' => 'general',
                    'key' => 'site_name',
                ])
            )
            ->assertOk()
            ->assertJsonPath(
                'data.value',
                'Eagle Global Hub LTD'
            )
            ->assertJsonPath(
                'data.type',
                'string'
            );
    }

    public function test_super_admin_can_create_or_update_setting(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'general',
                    'key' => 'maintenance_mode',
                ]),
                [
                    'value' => 'yes',
                    'type' => 'boolean',
                    'is_public' => false,
                ]
            )
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Setting saved successfully.'
            )
            ->assertJsonPath(
                'data.value',
                true
            )
            ->assertJsonPath(
                'data.type',
                'boolean'
            );

        $this->assertDatabaseHas('settings', [
            'group' => 'general',
            'key' => 'maintenance_mode',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function test_meta_pixel_id_must_be_numeric(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'analytics',
                    'key' => 'meta_pixel_id',
                ]),
                [
                    'value' => 'pixel-123<script>',
                    'type' => 'string',
                    'is_public' => true,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['value']);
    }

    public function test_meta_pixel_settings_keep_their_expected_types(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'analytics',
                    'key' => 'meta_pixel_enabled',
                ]),
                [
                    'value' => '1',
                    'type' => 'string',
                    'is_public' => true,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'analytics',
                    'key' => 'meta_pixel_id',
                ]),
                [
                    'value' => '123456789012345',
                    'type' => 'boolean',
                    'is_public' => true,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type']);
    }

    public function test_unsupported_setting_type_is_rejected(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'general',
                    'key' => 'invalid_type',
                ]),
                [
                    'value' => 'value',
                    'type' => 'object',
                    'is_public' => false,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'type',
            ]);
    }

    public function test_invalid_boolean_value_is_rejected(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->putJson(
                route('admin.settings.update', [
                    'group' => 'general',
                    'key' => 'bad_boolean',
                ]),
                [
                    'value' => 'maybe',
                    'type' => 'boolean',
                    'is_public' => false,
                ]
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'value',
            ]);
    }

    public function test_super_admin_can_delete_setting(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $this->actingAs($user)
            ->deleteJson(
                route('admin.settings.destroy', [
                    'group' => 'general',
                    'key' => 'site_name',
                ])
            )
            ->assertOk()
            ->assertJson([
                'message' => 'Setting deleted successfully.',
            ]);

        $this->assertDatabaseMissing('settings', [
            'group' => 'general',
            'key' => 'site_name',
        ]);
    }
}
