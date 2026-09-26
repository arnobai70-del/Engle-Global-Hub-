<?php

namespace Tests\Feature\Travel;

use App\Models\User;
use App\Services\Feature\FeatureManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelBookingStepPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_from_hotel_booking_steps(): void
    {
        foreach (['hotels.rooms', 'hotels.booking'] as $routeName) {
            $this->get(route($routeName))
                ->assertRedirect(route('login'));
        }
    }

    public function test_unverified_customer_is_redirected_to_email_verification(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('customer');

        foreach (['hotels.rooms', 'hotels.booking'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertRedirect(route('verification.notice'));
        }
    }

    public function test_user_without_permission_cannot_access_hotel_booking_steps(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        foreach (['hotels.rooms', 'hotels.booking'] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertForbidden();
        }
    }

    public function test_room_selection_page_stays_honest_without_provider_configuration(): void
    {
        $response = $this->actingAs($this->customer())
            ->get(route('hotels.rooms'))
            ->assertOk()
            ->assertSee('Hotel room availability is not configured')
            ->assertSee('Not Configured')
            ->assertSee('Room Selection')
            ->assertSee('Guest Details')
            ->assertDontSee('booking confirmed')
            ->assertDontSee('Sample Deluxe Room');

        $response->assertDontSee('server-only-test-key');
    }

    public function test_guest_details_page_stays_honest_without_provider_configuration(): void
    {
        $this->actingAs($this->customer())
            ->get(route('hotels.booking'))
            ->assertOk()
            ->assertSee('Hotel guest details and payment are not configured')
            ->assertSee('Not Configured')
            ->assertSee('Guest Details')
            ->assertDontSee('booking confirmed')
            ->assertDontSee('Payment successful')
            ->assertDontSee('Hotel summary');
    }

    public function test_local_preview_renders_sample_rooms_without_claiming_availability(): void
    {
        $this->app['env'] = 'local';

        $this->actingAs($this->customer())
            ->get(route('hotels.rooms'))
            ->assertOk()
            ->assertSee('Layout preview')
            ->assertSee('Sample Deluxe Room')
            ->assertSee('Sample')
            ->assertSee('Continue to guest details')
            ->assertSee(route('hotels.booking'), false)
            ->assertDontSee('booking confirmed')
            ->assertDontSee('reservation confirmed');
    }

    public function test_local_preview_renders_disabled_guest_form_and_sample_summary(): void
    {
        $this->app['env'] = 'local';

        $this->actingAs($this->customer())
            ->get(route('hotels.booking'))
            ->assertOk()
            ->assertSee('Layout preview')
            ->assertSee('Guest details')
            ->assertSee('Hotel summary')
            ->assertSee('Special requests')
            ->assertSeeText('Nothing on this page can be submitted')
            ->assertSee('disabled', false)
            ->assertDontSee('booking confirmed')
            ->assertDontSee('Payment successful')
            ->assertDontSee('charged successfully');
    }

    public function test_hotel_booking_steps_follow_the_hotel_feature_visibility(): void
    {
        $customer = $this->customer();

        app(FeatureManager::class)->update(
            'hotels',
            $this->state(authenticatedVisible: false),
        );

        foreach (['hotels.rooms', 'hotels.booking'] as $routeName) {
            $this->actingAs($customer)
                ->get(route($routeName))
                ->assertNotFound();
        }
    }

    private function customer(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return $user;
    }

    /**
     * @return array<string, bool|string|null>
     */
    private function state(
        bool $enabled = true,
        bool $publicVisible = true,
        bool $authenticatedVisible = true,
        bool $adminVisible = true,
        ?string $message = null,
    ): array {
        return [
            'enabled' => $enabled,
            'public_visible' => $publicVisible,
            'authenticated_visible' => $authenticatedVisible,
            'admin_visible' => $adminVisible,
            'message' => $message,
        ];
    }
}
