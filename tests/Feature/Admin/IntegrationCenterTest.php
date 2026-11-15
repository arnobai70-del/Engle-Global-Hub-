<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Integrations\IntegrationRegistry;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    /** @return array<string, array<string, mixed>> */
    private function integrations(): array
    {
        return collect(app(IntegrationRegistry::class)->all())->keyBy('key')->all();
    }

    public function test_unconfigured_providers_report_honest_status_and_manual_fallbacks(): void
    {
        config(['flight.search_provider' => 'unavailable', 'mail.default' => 'log']);
        $items = $this->integrations();

        $this->assertSame(IntegrationRegistry::STATUS_NOT_CONFIGURED, $items['flights']['status']);
        $this->assertSame('/flights/enquiries/create', $items['flights']['fallback_url']);
        $this->assertSame(IntegrationRegistry::STATUS_NOT_CONFIGURED, $items['hotels']['status']);
        $this->assertSame('/enquiries/hotel-booking', $items['hotels']['fallback_url']);
        $this->assertSame(IntegrationRegistry::STATUS_LOG_ONLY, $items['email']['status']);
        $this->assertSame(IntegrationRegistry::STATUS_NO_ADAPTER, $items['whatsapp']['status']);
        $this->assertSame(IntegrationRegistry::STATUS_NO_ADAPTER, $items['payments']['status']);
        $this->assertSame(IntegrationRegistry::STATUS_NO_ADAPTER, $items['education-partners']['status']);
    }

    public function test_configured_flight_provider_is_reported_without_exposing_the_secret(): void
    {
        config(['flight.search_provider' => 'duffel', 'flight.duffel.access_token' => 'duffel_test_SECRETVALUE', 'mail.default' => 'smtp']);
        $items = $this->integrations();

        $this->assertSame(IntegrationRegistry::STATUS_CONNECTED, $items['flights']['status']);
        $this->assertSame(IntegrationRegistry::STATUS_CONNECTED, $items['email']['status']);

        $this->actingAs($this->user('super-admin'))->get(route('admin.integrations.index'))
            ->assertOk()
            ->assertSee('DUFFEL_ACCESS_TOKEN')
            ->assertSee('No adapter yet — manual process')
            ->assertDontSee('duffel_test_SECRETVALUE');
    }

    public function test_only_super_admin_can_view_integrations(): void
    {
        $this->actingAs($this->user('admin'))->get(route('admin.integrations.index'))->assertForbidden();
        $this->actingAs($this->user('customer'))->get(route('admin.integrations.index'))->assertForbidden();
    }
}
