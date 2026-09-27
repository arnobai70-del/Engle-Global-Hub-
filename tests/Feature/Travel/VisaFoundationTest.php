<?php

namespace Tests\Feature\Travel;

use App\Contracts\Visa\VisaInformationProvider;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisaFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        foreach ([['Bangladesh','BD','BGD','+880'],['United Arab Emirates','AE','ARE','+971'],['India','IN','IND','+91']] as $country) {
            Country::query()->create(['name'=>$country[0],'iso2'=>$country[1],'iso3'=>$country[2],'phone_code'=>$country[3],'is_active'=>true]);
        }
    }

    public function test_guest_is_redirected_from_visa_service(): void { $this->get(route('visa.index'))->assertRedirect(route('login')); }

    public function test_verified_customer_sees_demo_preview_and_authority_disclaimer(): void
    {
        $this->actingAs($this->customer())->get(route('visa.index'))->assertOk()->assertSee('Demo Preview')->assertSee('Visa approval')->assertSee('relevant government authority');
    }

    public function test_user_without_permission_cannot_access_visa_service(): void
    {
        $user=User::factory()->create(['email_verified_at'=>now()]); $this->actingAs($user)->get(route('visa.index'))->assertForbidden();
    }

    public function test_unconfigured_requirement_lookup_returns_demo_guidance(): void
    {
        $this->actingAs($this->customer())->post(route('visa.requirements'),$this->validRequest())->assertOk()->assertSee('Demo Preview')->assertSee('Sample content shown for demonstration')->assertSee('Visa approval depends on the relevant authority');
    }

    public function test_configured_source_can_return_an_honest_no_data_state(): void
    {
        $this->configureProvider();
        $this->actingAs($this->customer())->post(route('visa.requirements'),$this->validRequest())->assertOk()->assertSee('No visa information was returned')->assertSeeText('Eligibility, documents and approval have not been assumed')->assertDontSee('server-only-test-key')->assertDontSee('Demo Preview');
    }

    public function test_configured_service_shows_real_trip_context_fields(): void
    {
        $this->configureProvider();
        $this->actingAs($this->customer())->get(route('visa.index'))->assertOk()->assertSee('Passport nationality')->assertSee('Origin country')->assertSee('Destination country')->assertSee('Departure')->assertSee('Arrival')->assertDontSee('server-only-test-key');
    }

    public function test_visa_trip_input_is_validated_before_provider_use(): void
    {
        $this->actingAs($this->customer())->post(route('visa.requirements'),['nationality'=>'BGD','origin_country'=>'ARE','destination_country'=>'ARE','departure_date'=>now()->addMonth()->toDateString(),'departure_time'=>'','arrival_date'=>now()->addMonth()->toDateString(),'arrival_time'=>'14:45'])->assertSessionHasErrors(['destination_country','departure_time']);
    }

    private function configureProvider(): void
    {
        config()->set('travel_services.services.visa.enabled',true); config()->set('travel_services.services.visa.provider','test-provider'); config()->set('travel_services.services.visa.providers.test-provider',EmptyVisaInformationProvider::class); config()->set('travel_services.services.visa.provider_requirements.test-provider',['credentials.api_key']); config()->set('travel_services.services.visa.credentials.api_key','server-only-test-key'); $this->app->forgetInstance(VisaInformationProvider::class);
    }

    private function customer(): User { $user=User::factory()->create(['email_verified_at'=>now()]); $user->assignRole('customer'); return $user; }
    private function validRequest(): array { $date=now()->addMonth()->toDateString(); return ['nationality'=>'BGD','origin_country'=>'BGD','destination_country'=>'ARE','departure_date'=>$date,'departure_time'=>'10:30','arrival_date'=>$date,'arrival_time'=>'14:45']; }
}

class EmptyVisaInformationProvider implements VisaInformationProvider { public function requirements(array $criteria): array { return []; } }
