<?php

namespace Tests\Feature\Travel;

use App\Contracts\Hotel\HotelSearchProvider;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTravelServiceAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_dashboard_shows_truthful_unconfigured_service_states_by_default(): void
    {
        $customer = $this->customer();
        $html = $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hotels')
            ->assertSee('Tours')
            ->assertSee('Visa')
            ->assertSee('Not Configured')
            ->assertSee('not configured for customer use')
            ->assertDontSee('Coming Soon')
            ->getContent();

        $this->assertIsString($html);
        $this->assertDoesNotMatchRegularExpression($this->serviceCardLinkPattern(route('hotels.index')), $html);
        $this->assertDoesNotMatchRegularExpression($this->serviceCardLinkPattern(route('tours.index')), $html);
        $this->assertDoesNotMatchRegularExpression($this->serviceCardLinkPattern(route('visa.index')), $html);
    }

    public function test_configured_service_becomes_a_dashboard_link_without_exposing_secrets(): void
    {
        $this->configureHotelProvider('dashboard-secret-must-not-render');

        $customer = $this->customer();
        $html = $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Search configured hotel availability')
            ->assertSee('Available')
            ->assertDontSee('dashboard-secret-must-not-render')
            ->getContent();

        $this->assertIsString($html);
        $this->assertMatchesRegularExpression($this->serviceCardLinkPattern(route('hotels.index')), $html);
    }

    public function test_configured_service_is_not_linked_without_customer_permission(): void
    {
        $this->configureHotelProvider('permission-test-secret');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $html = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('This service is not enabled for your account')
            ->assertSee('Unavailable')
            ->assertDontSee('permission-test-secret')
            ->getContent();

        $this->assertIsString($html);
        $this->assertDoesNotMatchRegularExpression($this->serviceCardLinkPattern(route('hotels.index')), $html);
    }

    private function serviceCardLinkPattern(string $url): string
    {
        return '/href="'.preg_quote($url, '/').'"\s+class="dashboard-service-card dashboard-service-card-link"/s';
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $customer->assignRole('customer');

        return $customer;
    }

    private function configureHotelProvider(string $apiKey): void
    {
        config()->set('travel_services.services.hotels.enabled', true);
        config()->set(
            'travel_services.services.hotels.provider',
            'dashboard-test-provider',
        );
        config()->set(
            'travel_services.services.hotels.providers.dashboard-test-provider',
            DashboardHotelSearchProvider::class,
        );
        config()->set(
            'travel_services.services.hotels.provider_requirements.dashboard-test-provider',
            ['credentials.api_key'],
        );
        config()->set(
            'travel_services.services.hotels.credentials.api_key',
            $apiKey,
        );
    }
}

class DashboardHotelSearchProvider implements HotelSearchProvider
{
    /**
     * @param  array<string, mixed>  $criteria
     * @return array<int, array<string, mixed>>
     */
    public function search(array $criteria): array
    {
        return [];
    }
}
