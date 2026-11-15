<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\ServiceEnquiry;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceDestinationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceEnquiryTest extends TestCase
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

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return [
            'full_name' => 'Tourist Person', 'email' => 'Tourist@Example.com', 'phone' => '+8801712345678',
            'destination_country_id' => Country::query()->where('iso2', 'TH')->value('id'),
            'travel_date' => now()->addMonth()->toDateString(), 'return_date' => now()->addMonth()->addWeek()->toDateString(),
            'travellers' => 2, 'message' => 'Family trip', 'consent' => '1', ...$overrides,
        ];
    }

    public function test_tourist_visa_form_lists_only_its_destinations_and_unknown_services_404(): void
    {
        $this->get(route('service-enquiries.create', 'tourist-visa'))->assertOk()
            ->assertSee('Tourist Visa enquiry')->assertSee('Thailand')->assertDontSee('Belarus')
            ->assertSee('does not book, reserve or charge anything');
        $this->get(route('service-enquiries.create', 'bus'))->assertOk()->assertSee('Route or destination');
        $this->get('/enquiries/student-visa')->assertNotFound();
        $this->get('/enquiries/air-ticket')->assertNotFound();
        $this->get('/enquiries/spaceship')->assertNotFound();
    }

    public function test_guest_enquiry_is_saved_and_signed_in_customer_sees_own_enquiries_only(): void
    {
        $this->post(route('service-enquiries.store', 'tourist-visa'), $this->payload())
            ->assertRedirect(route('service-enquiries.create', 'tourist-visa'))->assertSessionHas('enquiry_reference');
        $guestEnquiry = ServiceEnquiry::query()->sole();
        $this->assertNull($guestEnquiry->user_id);
        $this->assertSame('tourist@example.com', $guestEnquiry->email);
        $this->assertSame('tourist_visa', $guestEnquiry->service->value);

        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('service-enquiries.store', 'bus'), $this->payload([
            'destination_country_id' => null, 'route_or_destination' => "Dhaka to Cox's Bazar",
        ]))->assertRedirect();

        $this->actingAs($customer)->get(route('service-enquiries.mine'))->assertOk()
            ->assertSee('Dhaka to Cox&#039;s Bazar', false)->assertDontSee($guestEnquiry->reference);
    }

    public function test_invalid_enquiries_are_rejected(): void
    {
        $this->post(route('service-enquiries.store', 'tourist-visa'), $this->payload([
            'destination_country_id' => Country::query()->where('iso2', 'BY')->value('id'),
        ]))->assertSessionHasErrors('destination_country_id');
        $this->post(route('service-enquiries.store', 'tourist-visa'), $this->payload(['travel_date' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('travel_date');
        $this->post(route('service-enquiries.store', 'tourist-visa'), $this->payload(['consent' => null]))->assertSessionHasErrors('consent');
        $this->post(route('service-enquiries.store', 'tourist-visa'), $this->payload(['website' => 'spam']))->assertSessionHasErrors('website');

        $this->assertDatabaseCount('service_enquiries', 0);
    }

    public function test_staff_manage_enquiries_and_customers_cannot(): void
    {
        $this->post(route('service-enquiries.store', 'hotel-booking'), $this->payload(['destination_country_id' => null]));
        $enquiry = ServiceEnquiry::query()->sole();
        $customer = $this->user('customer');

        $this->actingAs($customer)->get(route('admin.service-enquiries.index'))->assertForbidden();
        $this->actingAs($customer)->patch(route('admin.service-enquiries.update', $enquiry), ['status' => 'closed'])->assertForbidden();

        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.service-enquiries.index', ['service' => 'hotel_booking']))
            ->assertOk()->assertSee($enquiry->reference)->assertSee('Hotel Booking');
        $this->actingAs($admin)->patch(route('admin.service-enquiries.update', $enquiry), [
            'status' => 'quotation_sent', 'assigned_to_user_id' => $admin->id, 'internal_notes' => 'Sent 3 hotel options',
        ])->assertRedirect();

        $this->assertSame('quotation_sent', $enquiry->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'service_enquiry.updated']);
    }
}
