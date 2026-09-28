<?php

namespace Tests\Feature\Travel;

use App\Contracts\Hotel\HotelSearchProvider;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_can_browse_hotel_landing_page(): void
    {
        $this->get(route('hotels.index'))
            ->assertOk()
            ->assertSee('Demo Preview');
    }

    public function test_verified_customer_sees_demo_preview_when_provider_is_unavailable(): void
    {
        $this->actingAs($this->customer())->get(route('hotels.index'))
            ->assertOk()->assertSee('Demo Preview')->assertSee('Sample from price')->assertSee('View Demo Results')
            ->assertDontSee('booking confirmed');
    }

    public function test_user_without_permission_can_browse_landing_but_cannot_search(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('hotels.index'))
            ->assertOk()
            ->assertSee('Demo Preview');

        $this->actingAs($user)
            ->post(route('hotels.search'), $this->validSearch())
            ->assertForbidden();
    }

    public function test_unconfigured_search_returns_clearly_labelled_demo_results(): void
    {
        $this->actingAs($this->customer())->post(route('hotels.search'), $this->validSearch())
            ->assertOk()->assertSee('Demo Preview')->assertSee('Sample content shown for demonstration')
            ->assertDontSee('booking confirmed');
    }

    public function test_configured_provider_can_return_an_honest_empty_result(): void
    {
        config()->set('travel_services.services.hotels.enabled', true);
        config()->set('travel_services.services.hotels.provider', 'test-provider');
        config()->set('travel_services.services.hotels.providers.test-provider', EmptyHotelSearchProvider::class);
        config()->set('travel_services.services.hotels.provider_requirements.test-provider', ['credentials.api_key']);
        config()->set('travel_services.services.hotels.credentials.api_key', 'server-only-test-key');
        $this->app->forgetInstance(HotelSearchProvider::class);

        $this->actingAs($this->customer())->post(route('hotels.search'), $this->validSearch())
            ->assertOk()->assertSee('No hotel stays were returned')->assertSee('No availability or price has been assumed')
            ->assertDontSee('server-only-test-key')->assertDontSee('Demo Preview');
    }

    public function test_hotel_search_input_is_validated_before_provider_use(): void
    {
        $this->actingAs($this->customer())->post(route('hotels.search'), ['destination'=>'','check_in'=>now()->subDay()->toDateString(),'check_out'=>now()->subDays(2)->toDateString(),'adults'=>0,'rooms'=>0])
            ->assertSessionHasErrors(['destination','check_in','check_out','adults','rooms']);
    }

    private function customer(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('customer');
        return $user;
    }

    private function validSearch(): array
    {
        return ['destination'=>'Dhaka','check_in'=>now()->addWeek()->toDateString(),'check_out'=>now()->addWeek()->addDays(2)->toDateString(),'adults'=>2,'rooms'=>1];
    }
}

class EmptyHotelSearchProvider implements HotelSearchProvider
{
    public function search(array $criteria): array { return []; }
}
