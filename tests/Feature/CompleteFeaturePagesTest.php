<?php

namespace Tests\Feature;

use App\Contracts\Hotel\HotelSearchProvider;
use App\Models\User;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\ServiceCatalog;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteFeaturePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, CountrySeeder::class, ServiceDestinationSeeder::class]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_every_catalogued_service_has_its_own_page_with_breadcrumb_status_and_a_working_action(): void
    {
        foreach (app(ServiceCatalog::class)->all() as $slug => $service) {
            $html = (string) $this->get(route('services.show', $slug))->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/<h1 id="service-title">[^<]+<\/h1>/', $html, $slug);
            $this->assertStringContainsString('class="v2-crumbs"', $html, $slug);
            $this->assertStringContainsString('Enquiry service', $html, $slug);
            $this->assertStringNotContainsString('href="#"', $html, $slug);
            $this->assertStringContainsString('class="v2-btn v2-btn-red" href="#quote-form"', $html, $slug.' has no primary action');
            $this->assertStringContainsString('action="'.route('leads.quote', $slug).'"', $html, $slug);
        }

        $this->get(route('services.show', 'not-a-service'))->assertNotFound();
        $this->get(route('services.index'))->assertRedirect(route('travel-services.index'));
    }

    public function test_service_pages_keep_the_pdf_country_catalogues_and_preselect_the_enquiry(): void
    {
        $tourist = (string) $this->get(route('services.show', 'tourist-visa'))->assertOk()->getContent();
        foreach (['India', 'Sri Lanka', 'Thailand', 'Malaysia', 'Singapore', 'China', 'Philippines', 'Egypt', 'Dubai (United Arab Emirates)', 'Vietnam', 'South Korea', 'Pakistan', 'Indonesia', 'Cambodia', 'Canada', 'Germany', 'Sweden', 'United Kingdom', 'United States', 'France', 'Austria'] as $country) {
            $this->assertStringContainsString($country, $tourist);
        }
        $this->assertStringContainsString('<option value="Thailand"', $tourist);

        $student = (string) $this->get(route('services.show', 'student-visa'))->getContent();
        foreach (['Malaysia', 'Bulgaria', 'United Kingdom', 'Japan', 'South Korea', 'Cyprus'] as $country) {
            $this->assertStringContainsString($country, $student);
        }

        $this->get(route('services.show', 'group-tours'))->assertSee('action="'.route('leads.quote', 'group-tours').'"', false);
        $this->get(route('service-enquiries.create', ['service' => 'tourist-visa', 'destination' => 'TH']))->assertOk()->assertSee('selected', false);
    }

    public function test_api_dependent_pages_never_show_technical_status_and_offer_an_enquiry(): void
    {
        foreach (['trains', 'air-ticket'] as $slug) {
            $this->get(route('services.show', $slug))->assertOk()
                ->assertDontSee('Waiting for API')
                ->assertSee('Enquiry service')
                ->assertSee('action="'.route('leads.quote', $slug).'"', false);
        }

        $this->get(route('map'))->assertOk()->assertDontSee('Waiting for API')->assertSee('Coming soon')->assertSee('Interactive map');
        $this->get(route('mobile-app'))->assertOk()->assertDontSee('Waiting for API')->assertSee('Coming soon')->assertSee('disabled', false);
    }

    public function test_travel_pages_only_claim_a_live_provider_after_a_connection_test(): void
    {
        config()->set('travel_services.services.hotels.enabled', true);
        config()->set('travel_services.services.hotels.provider', 'test-provider');
        config()->set('travel_services.services.hotels.providers.test-provider', UntestedHotelSearchProvider::class);
        config()->set('travel_services.services.hotels.provider_requirements.test-provider', ['credentials.api_key']);
        config()->set('travel_services.services.hotels.credentials.api_key', 'configured-but-untested');

        $customer = $this->user('customer');
        $this->actingAs($customer)->get(route('hotels.index'))->assertOk()->assertSee('id="quote-form"', false)->assertDontSee('Live provider connected')->assertDontSee('Search online');

        app(ProviderStatus::class)->recordVerified('hotels');
        $this->actingAs($customer)->get(route('hotels.index'))->assertOk()->assertSee('Live provider connected')->assertDontSee('data-api-notice="hotels"', false);

        $this->actingAs($this->user('super-admin'))->get(route('flights.index'))->assertOk()
            ->assertDontSee('data-api-notice="flights"', false)
            ->assertSee('action="'.route('leads.quote', 'air-ticket').'"', false);
    }

    public function test_provider_status_is_never_live_without_a_recorded_connection_test(): void
    {
        $status = app(ProviderStatus::class);

        foreach (array_keys(ProviderStatus::CAPABILITIES) as $capability) {
            $this->assertNotSame(ProviderStatus::LIVE, $status->of($capability), $capability.' must not be live without a test');
        }

        config(['flight.search_provider' => 'duffel', 'flight.duffel.access_token' => 'test-token-not-verified']);
        $this->assertSame(ProviderStatus::WAITING, $status->of('flights'));

        $status->recordVerified('flights');
        $this->assertSame(ProviderStatus::LIVE, $status->of('flights'));

        config(['flight.duffel.access_token' => 'a-different-token']);
        $this->assertSame(ProviderStatus::WAITING, $status->of('flights'), 'changing the key must require a new connection test');

        config(['flight.search_provider' => 'fixture']);
        $this->assertSame(ProviderStatus::DEMO, $status->of('flights'), 'fixture data is only ever a demo');
    }

    public function test_admin_sees_the_public_provider_status_of_every_api_feature(): void
    {
        $html = (string) $this->actingAs($this->user('super-admin'))->get(route('admin.integrations.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Public status on the website', $html);
        foreach (array_keys(ProviderStatus::CAPABILITIES) as $capability) {
            $this->assertStringContainsString('data-capability="'.$capability.'"', $html);
        }
        $this->assertStringContainsString('WAITING FOR API', $html);
    }

    public function test_error_pages_are_branded_translated_and_not_indexed(): void
    {
        $this->get('/no-such-page-anywhere')->assertNotFound()
            ->assertSee('Page not found')->assertSee('noindex,follow', false)->assertSee('images/eagle-global-hub-logo.png', false);

        $this->get('/no-such-page-anywhere?lang=bn')->assertNotFound()->assertSee('পেজটি পাওয়া যায়নি');

        $this->actingAs($this->user('customer'))->get(route('admin.content.index'))->assertForbidden();
    }

    public function test_public_pages_use_the_original_logo_without_distortion_and_guard_double_submits(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('src="'.asset('images/eagle-global-hub-logo.png').'"', $html);
        $this->assertStringContainsString('width="240" height="241"', $html);
        $this->assertStringContainsString('data-sending-label=', $html);
        $this->assertSame('a5ac288e688d1b65', substr(hash_file('sha256', public_path('images/eagle-global-hub-logo.png')), 0, 16), 'the original logo file must not be replaced');

        $script = (string) file_get_contents(public_path('js/egh-sg-nav.js'));
        $this->assertStringContainsString("form.dataset.submitted === '1'", $script);
    }
}

class UntestedHotelSearchProvider implements HotelSearchProvider
{
    public function search(array $criteria): array
    {
        return [];
    }
}
