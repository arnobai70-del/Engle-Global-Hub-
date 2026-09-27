<?php

namespace Tests\Feature\Travel;

use App\Contracts\Hotel\HotelSearchProvider;
use App\Contracts\Tour\TourSearchProvider;
use App\Contracts\Visa\VisaInformationProvider;
use App\Services\Travel\TravelServiceRegistry;
use Tests\TestCase;

class HomepageTravelServiceAvailabilityTest extends TestCase
{
    public function test_homepage_shows_demo_service_links_by_default(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Flights')
            ->assertSee('Hotels')
            ->assertSee('Tours')
            ->assertSee('Visa')
            ->assertSee('Demo Preview')
            ->assertSee('href="'.route('hotels.index').'"', false)
            ->assertSee('href="'.route('tours.index').'"', false)
            ->assertSee('href="'.route('visa.index').'"', false)
            ->assertDontSee('MetaFore');
    }

    public function test_enabled_service_without_required_provider_configuration_stays_demo_but_keeps_page_link(): void
    {
        $this->configureHotelProvider(apiKey: null);

        $services = app(TravelServiceRegistry::class)->all();

        $this->assertFalse($services['hotels']['available']);
        $this->assertTrue($services['hotels']['demo_mode']);
        $this->assertSame('Demo Preview', $services['hotels']['display_status']);
        $this->assertSame('hotels.index', $services['hotels']['page_route_name']);
        $this->assertNull($services['hotels']['route_name']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hotels')
            ->assertSee('Demo Preview')
            ->assertSee('href="'.route('hotels.index').'"', false);
    }

    public function test_safely_configured_service_becomes_live_without_exposing_secrets(): void
    {
        $this->configureHotelProvider(
            apiKey: 'homepage-must-never-render-this-secret'
        );

        $this->assertTrue(
            config('travel_services.services.hotels.enabled')
        );
        $this->assertSame(
            'test-provider',
            config('travel_services.services.hotels.provider')
        );
        $this->assertTrue(
            is_a(
                config(
                    'travel_services.services.hotels.providers.test-provider'
                ),
                HotelSearchProvider::class,
                true
            )
        );
        $this->assertSame(
            ['credentials.api_key'],
            config(
                'travel_services.services.hotels.provider_requirements.test-provider'
            )
        );

        $services = app(TravelServiceRegistry::class)->all();
        $this->assertTrue($services['hotels']['available']);
        $this->assertFalse($services['hotels']['demo_mode']);
        $this->assertSame('Live Provider', $services['hotels']['display_status']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hotels')
            ->assertSee('Live Provider')
            ->assertSee('href="'.route('hotels.index').'"', false)
            ->assertDontSee('homepage-must-never-render-this-secret');
    }

    public function test_all_configured_services_render_real_links_without_rendering_api_keys(): void
    {
        $this->configureHotelProvider('hotel-server-key');
        $this->configureProvider(
            'tours',
            HomepageTourSearchProvider::class,
            'tour-server-key'
        );
        $this->configureProvider(
            'visa',
            HomepageVisaInformationProvider::class,
            'visa-server-key'
        );

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('hotels.index').'"', false)
            ->assertSee('href="'.route('tours.index').'"', false)
            ->assertSee('href="'.route('visa.index').'"', false);

        foreach ([
            'hotel-server-key',
            'tour-server-key',
            'visa-server-key',
        ] as $secret) {
            $response->assertDontSee($secret);
        }
    }

    private function configureHotelProvider(?string $apiKey): void
    {
        $this->configureProvider(
            'hotels',
            HomepageHotelSearchProvider::class,
            $apiKey
        );
    }

    /**
     * @param  class-string  $providerClass
     */
    private function configureProvider(
        string $service,
        string $providerClass,
        ?string $apiKey,
    ): void {
        config()->set("travel_services.services.{$service}.enabled", true);
        config()->set(
            "travel_services.services.{$service}.provider",
            'test-provider'
        );
        config()->set(
            "travel_services.services.{$service}.providers.test-provider",
            $providerClass
        );
        config()->set(
            "travel_services.services.{$service}.provider_requirements.test-provider",
            ['credentials.api_key']
        );
        config()->set(
            "travel_services.services.{$service}.credentials.api_key",
            $apiKey
        );
    }
}

class HomepageHotelSearchProvider implements HotelSearchProvider
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

class HomepageTourSearchProvider implements TourSearchProvider
{
    public function search(array $criteria): array
    {
        return [];
    }
}

class HomepageVisaInformationProvider implements VisaInformationProvider
{
    public function requirements(array $criteria): array
    {
        return [];
    }
}
