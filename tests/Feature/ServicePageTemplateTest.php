<?php

namespace Tests\Feature;

use App\Contracts\Hotel\HotelSearchProvider;
use App\Models\User;
use App\Services\Integrations\ProviderStatus;
use App\Services\SettingService;
use App\Services\Travel\ServicePageData;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServicePageTemplateTest extends TestCase
{
    use RefreshDatabase;

    private const DEVELOPER_WORDING = ['Waiting for API', 'WAITING FOR API', 'provider API is connected', 'Demo Preview', 'demo preview', 'Sample content', 'Live visa requirement lookup', 'Live Provider'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        app(SettingService::class)->set('contact', 'phone', '01953626481', 'string', true);
    }

    public function test_every_service_page_uses_the_template_with_a_quote_form_and_no_developer_wording(): void
    {
        $pages = app(ServicePageData::class);

        foreach ($pages->slugs() as $slug) {
            $response = $this->get($pages->url($slug))->assertOk();
            $html = (string) $response->getContent();

            $page = $pages->for($slug);
            $this->assertStringContainsString('action="'.route('leads.quote', $page['quote_service']).'"', $html, $slug);
            $this->assertStringContainsString('https://wa.me/8801953626481', $html, $slug);
            $this->assertStringContainsString('What&#039;s included', $html, $slug);
            $this->assertStringContainsString('Documents you&#039;ll usually need', $html, $slug);
            foreach (self::DEVELOPER_WORDING as $wording) {
                $this->assertStringNotContainsString($wording, $html, $slug.' shows "'.$wording.'"');
            }
        }
    }

    public function test_hotels_tours_visa_and_flights_are_public_enquiry_pages_for_guests(): void
    {
        foreach (['hotels.index', 'tours.index', 'visa.index', 'flights.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Send an enquiry')->assertSee('id="quote-form"', false);
        }
        $this->get(route('travel-services.index'))->assertOk()->assertDontSee('What the labels mean')->assertDontSee('demo preview');
        $this->get(route('work-visa.apply'))->assertRedirect(route('work-visa.index').'#quote-form');
    }

    public function test_fee_and_reply_time_tiles_come_only_from_business_facts(): void
    {
        $this->get(route('services.show', 'tourist-visa'))->assertSee('Free quote')->assertDontSee('Reply time')->assertDontSee('[X]');

        app(SettingService::class)->set('facts', 'fee_tourist_visa', '3,500', 'string', true);
        app(SettingService::class)->set('facts', 'reply_time', 'Within 1 working day', 'string', true);

        $this->get(route('services.show', 'tourist-visa'))->assertSee('From BDT 3,500')->assertSee('Within 1 working day')->assertDontSee('Free quote');
    }

    public function test_tourist_visa_headline_counts_the_countries_staff_manage(): void
    {
        $this->seed([CountrySeeder::class, ServiceDestinationSeeder::class]);

        $this->get(route('services.show', 'tourist-visa'))->assertOk()
            ->assertSeeText('Tourist visa help for')
            ->assertSee('Countries we help with')
            ->assertSee('<option value="Thailand"', false);
    }

    public function test_a_live_provider_brings_back_online_search_for_customers_who_may_search(): void
    {
        config()->set('travel_services.services.hotels.enabled', true);
        config()->set('travel_services.services.hotels.provider', 'test-provider');
        config()->set('travel_services.services.hotels.providers.test-provider', TemplateTestHotelProvider::class);
        config()->set('travel_services.services.hotels.provider_requirements.test-provider', ['credentials.api_key']);
        config()->set('travel_services.services.hotels.credentials.api_key', 'test-key');
        app(ProviderStatus::class)->recordVerified('hotels');

        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $this->actingAs($customer)->get(route('hotels.index'))->assertOk()->assertSee('action="'.route('hotels.search').'"', false);
        auth()->logout();
        $this->get(route('hotels.index'))->assertOk()->assertSee('Search online')->assertSee('id="quote-form"', false);
    }
}

class TemplateTestHotelProvider implements HotelSearchProvider
{
    public function search(array $criteria): array
    {
        return [];
    }
}
