<?php

namespace Tests\Feature\Travel;

use App\Contracts\Tour\TourSearchProvider;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourFoundationTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(RolePermissionSeeder::class); }

    public function test_guest_can_browse_tour_landing_page(): void
    {
        $this->get(route('tours.index'))->assertOk()->assertSee('Demo Preview');
    }

    public function test_verified_customer_sees_demo_preview_when_provider_is_unavailable(): void
    {
        $this->actingAs($this->customer())->get(route('tours.index'))->assertOk()->assertSee('Demo Preview')->assertSee('View Demo Activities');
    }

    public function test_user_without_permission_can_browse_landing_but_cannot_search(): void
    {
        $user=User::factory()->create(['email_verified_at'=>now()]);
        $this->actingAs($user)->get(route('tours.index'))->assertOk()->assertSee('Demo Preview');
        $this->actingAs($user)->post(route('tours.search'), $this->validSearch())->assertForbidden();
    }

    public function test_unconfigured_tour_search_returns_demo_results(): void
    {
        $this->actingAs($this->customer())->post(route('tours.search'), $this->validSearch())->assertOk()->assertSee('Demo Preview')->assertSee('Sample content shown for demonstration')->assertDontSee('booking confirmed');
    }

    public function test_configured_provider_can_return_an_honest_empty_result(): void
    {
        config()->set('travel_services.services.tours.enabled',true); config()->set('travel_services.services.tours.provider','test-provider'); config()->set('travel_services.services.tours.providers.test-provider',EmptyTourSearchProvider::class); config()->set('travel_services.services.tours.provider_requirements.test-provider',['credentials.api_key']); config()->set('travel_services.services.tours.credentials.api_key','server-only-test-key'); $this->app->forgetInstance(TourSearchProvider::class);
        $this->actingAs($this->customer())->post(route('tours.search'),$this->validSearch())->assertOk()->assertSee('No tours were returned')->assertSeeText('No availability, price or booking has been assumed')->assertDontSee('server-only-test-key')->assertDontSee('Demo Preview');
    }

    public function test_tour_search_input_is_validated_before_provider_use(): void
    {
        $this->actingAs($this->customer())->post(route('tours.search'),['destination'=>'','travel_date'=>now()->subDay()->toDateString(),'travelers'=>0])->assertSessionHasErrors(['destination','travel_date','travelers']);
    }

    private function customer(): User { $user=User::factory()->create(['email_verified_at'=>now()]); $user->assignRole('customer'); return $user; }
    private function validSearch(): array { return ['destination'=>'Coxs Bazar','travel_date'=>now()->addWeek()->toDateString(),'travelers'=>2]; }
}

class EmptyTourSearchProvider implements TourSearchProvider { public function search(array $criteria): array { return []; } }
