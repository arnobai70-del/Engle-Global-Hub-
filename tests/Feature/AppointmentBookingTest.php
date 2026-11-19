<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\StudentEnquiry;
use App\Models\User;
use App\Notifications\AppointmentBooked;
use App\Services\Consultancy\BookingSlots;
use App\Services\SettingService;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Tuesday 10 Nov 2026, 08:00 in Dhaka.
        $this->travelTo(CarbonImmutable::parse('2026-11-10 08:00', BookingSlots::OFFICE_TIMEZONE));
    }

    public function test_booking_is_hidden_until_admin_sets_all_opening_hours(): void
    {
        $this->get(route('appointments.book'))->assertRedirect(route('consultancy.malaysia').'#apply');

        $this->hours(['days' => '2', 'start' => '10:00', 'end' => '12:00', 'slot_minutes' => '30', 'days_ahead' => '7', 'modes' => '']);
        $this->get(route('appointments.book'))->assertRedirect(route('consultancy.malaysia').'#apply');

        $this->hours(['modes' => 'office,phone']);
        $this->get(route('appointments.book'))->assertOk()->assertSee('Book a free consultation');
    }

    public function test_free_times_follow_the_opening_hours_and_skip_taken_slots(): void
    {
        // Tuesdays 10:00 to 12:00, 30-minute slots.
        $this->hours(['days' => '2', 'start' => '10:00', 'end' => '12:00', 'slot_minutes' => '30', 'days_ahead' => '7', 'modes' => 'office']);
        $this->appointment('2026-11-10 10:30:00', Appointment::STATUS_CONFIRMED);
        $this->appointment('2026-11-10 11:00:00', Appointment::STATUS_CANCELLED);

        $this->assertSame([
            '2026-11-10' => ['10:00', '11:00', '11:30'],
            '2026-11-17' => ['10:00', '10:30', '11:00', '11:30'],
        ], app(BookingSlots::class)->free());

        $this->travelTo(CarbonImmutable::parse('2026-11-10 10:45', BookingSlots::OFFICE_TIMEZONE));
        $this->assertSame(['11:00', '11:30'], app(BookingSlots::class)->free()['2026-11-10'], 'past times are not offered');
    }

    public function test_booking_creates_an_enquiry_and_a_confirmed_appointment_and_emails_the_visitor(): void
    {
        Notification::fake();
        $this->hours(['days' => '2', 'start' => '10:00', 'end' => '12:00', 'slot_minutes' => '30', 'days_ahead' => '7', 'modes' => 'office,video']);

        $this->post(route('appointments.book.store'), $this->booking(['slot' => '2026-11-10 11:00', 'mode' => 'video']))
            ->assertRedirect(route('appointments.book'))->assertSessionHas('booking');

        $appointment = Appointment::query()->with('enquiry')->sole();
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame('2026-11-10 11:00', $appointment->scheduled_at->format('Y-m-d H:i'));
        $this->assertSame(30, $appointment->duration_minutes);
        $this->assertNull($appointment->staff_user_id);
        $this->assertSame('Nadia Islam', $appointment->enquiry?->full_name);
        $this->assertSame('nadia@example.com', $appointment->enquiry?->email);
        $this->assertNotContains('11:00', app(BookingSlots::class)->free()['2026-11-10'], 'the booked time is no longer offered');

        Notification::assertSentOnDemand(
            AppointmentBooked::class,
            fn (AppointmentBooked $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'nadia@example.com' && $notification->appointment->is($appointment),
        );
    }

    public function test_a_taken_or_unlisted_time_cannot_be_booked(): void
    {
        Notification::fake();
        $this->hours(['days' => '2', 'start' => '10:00', 'end' => '12:00', 'slot_minutes' => '30', 'days_ahead' => '7', 'modes' => 'office']);

        $this->post(route('appointments.book.store'), $this->booking(['slot' => '2026-11-10 10:00']))->assertSessionHasNoErrors();
        $this->post(route('appointments.book.store'), $this->booking(['slot' => '2026-11-10 10:00', 'email' => 'second@example.com']))->assertSessionHasErrors('slot');
        $this->post(route('appointments.book.store'), $this->booking(['slot' => '2026-11-10 13:00']))->assertSessionHasErrors('slot');
        $this->post(route('appointments.book.store'), $this->booking(['slot' => '2026-11-10 10:30', 'mode' => 'video']))->assertSessionHasErrors('mode');

        $this->assertSame(1, Appointment::query()->count());
        $this->assertSame(1, StudentEnquiry::query()->count());
    }

    public function test_admin_sets_booking_hours_on_the_appointments_page(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('admin.appointments.index'))->assertOk()->assertSee('Public booking hours')->assertSee('Public booking is OFF');
        $this->actingAs($admin)->patch(route('admin.appointments.booking-hours'), ['days' => [6, 7], 'start' => '18:00', 'end' => '10:00'])->assertSessionHasErrors('end');
        $this->actingAs($admin)->patch(route('admin.appointments.booking-hours'), [
            'days' => [6, 7, 1], 'start' => '10:00', 'end' => '17:00', 'slot_minutes' => 45, 'days_ahead' => 14, 'modes' => ['office', 'phone'],
        ])->assertSessionHasNoErrors();

        $settings = app(BookingSlots::class)->settings();
        $this->assertSame([6, 7, 1], $settings['days']);
        $this->assertSame(45, $settings['slot_minutes']);
        $this->assertTrue(app(BookingSlots::class)->isConfigured());
    }

    /**
     * @param  array<string, string>  $values
     */
    private function hours(array $values): void
    {
        foreach ($values as $key => $value) {
            app(SettingService::class)->set(BookingSlots::GROUP, $key, $value, 'string', false);
        }
    }

    private function appointment(string $at, string $status): void
    {
        $appointment = new Appointment(['scheduled_at' => $at, 'duration_minutes' => 30, 'mode' => 'phone']);
        $appointment->status = $status;
        $appointment->save();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function booking(array $overrides): array
    {
        return array_merge(['mode' => 'office', 'full_name' => 'Nadia Islam', 'phone' => '01712345678', 'email' => 'nadia@example.com', 'consent' => '1'], $overrides);
    }
}
