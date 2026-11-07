<?php

namespace Tests\Feature\Flight;

use App\Models\FlightEnquiry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ManualFlightEnquiryTest extends TestCase
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

    private function payload(): array
    {
        return [
            'trip_type' => 'one_way', 'origin' => 'dac', 'destination' => 'kol',
            'departure_date' => now()->addDays(10)->toDateString(),
            'adults' => 1, 'children' => 0, 'infants' => 0,
            'cabin_class' => 'economy',
            'contact_name' => 'Enquiry Customer',
            'contact_email' => 'customer@example.com',
            'contact_phone' => '+8801712345678',
            'consent' => '1',
        ];
    }

    public function test_guest_cannot_submit_or_view_customer_enquiries(): void
    {
        $this->get(route('flights.enquiries.create'))->assertRedirect(route('login'));
        $this->post(route('flights.enquiries.store'), $this->payload())->assertRedirect(route('login'));
    }

    public function test_customer_enquiry_persists_without_booking_or_fabricated_fare(): void
    {
        $user = $this->user('customer');
        $this->actingAs($user)->get(route('flights.enquiries.create'))->assertOk()->assertSee('Request a Flight Fare');
        $this->actingAs($user)->post(route('flights.enquiries.store'), $this->payload())
            ->assertRedirect();

        $enquiry = FlightEnquiry::query()->sole();
        $this->assertSame($user->id, $enquiry->user_id);
        $this->assertSame('DAC', $enquiry->origin);
        $this->assertSame('KOL', $enquiry->destination);
        $this->assertSame('new_enquiry', $enquiry->status);
        $this->assertNull($enquiry->quoted_total_bdt);
        $this->actingAs($user)->get(route('flights.enquiries.show', $enquiry))
            ->assertOk()->assertSee($enquiry->reference)->assertSee('No fare has been quoted yet');
    }

    public function test_unauthorized_customer_cannot_view_other_customers_enquiry(): void
    {
        $first = $this->user('customer');
        $second = $this->user('customer');
        $this->actingAs($first)->post(route('flights.enquiries.store'), $this->payload());
        $enquiry = FlightEnquiry::query()->sole();

        $this->actingAs($second)->get(route('flights.enquiries.show', $enquiry))->assertForbidden();
        $this->actingAs($second)->get(route('admin.flight-enquiries.index'))->assertForbidden();
    }

    public function test_invalid_date_and_missing_consent_are_rejected(): void
    {
        $user = $this->user('customer');
        $payload = $this->payload();
        $payload['departure_date'] = now()->subDay()->toDateString();
        unset($payload['consent']);
        $this->actingAs($user)->post(route('flights.enquiries.store'), $payload)
            ->assertSessionHasErrors(['departure_date', 'consent']);
        $this->assertDatabaseCount('flight_enquiries', 0);
    }

    public function test_admin_can_quote_bdt_and_update_manual_status(): void
    {
        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('flights.enquiries.store'), $this->payload());
        $enquiry = FlightEnquiry::query()->sole();

        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.flight-enquiries.index'))
            ->assertOk()->assertSee($enquiry->reference);
        $this->actingAs($admin)->patch(route('admin.flight-enquiries.update', $enquiry), [
            'status' => 'quotation_sent', 'quoted_total_bdt' => '12500.00',
            'admin_notes' => 'Manual fare checked with the issuer',
        ])->assertRedirect();

        $enquiry->refresh();
        $this->assertSame('quotation_sent', $enquiry->status);
        $this->assertSame('12500.00', $enquiry->quoted_total_bdt);
        $this->actingAs($customer)->get(route('flights.enquiries.show', $enquiry))
            ->assertOk()->assertSee('12,500.00');
    }

    public function test_admin_cannot_mark_quoted_without_bdt_amount(): void
    {
        $customer = $this->user('customer');
        $this->actingAs($customer)->post(route('flights.enquiries.store'), $this->payload());
        $enquiry = FlightEnquiry::query()->sole();

        $this->actingAs($this->user('admin'))->patch(route('admin.flight-enquiries.update', $enquiry), [
            'status' => 'quotation_sent',
        ])->assertSessionHasErrors(['quoted_total_bdt']);
        $this->assertSame('new_enquiry', $enquiry->fresh()->status);
    }
}
