<?php

namespace Tests\Feature\Flight;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Screen 3 contract: the flight booking traveller-details step.
 *
 * The traveller form itself is generated in the browser by the flight search
 * script, so the reachable part of the contract is the page it lands on plus
 * the two files that shape it: the journey rail is asserted from the response,
 * and the layout and wording are asserted from the source files that ship.
 *
 * The point of these assertions is that the step stays a presentation layer
 * over the existing booking flow. It may rearrange and label what the booking
 * script already rendered, but it must not edit a field, invent a traveller,
 * reach for a provider or claim a booking, ticket or payment that was never
 * made.
 */
final class FlightTravellerDetailsUiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(
            RolePermissionSeeder::class
        );
    }

    public function test_traveller_step_ships_a_journey_rail_the_script_can_move(): void
    {
        $response = $this
            ->actingAs($this->customer())
            ->get(route('flights.index'));

        $response
            ->assertOk()
            ->assertSee('data-flight-journey-steps', false)
            ->assertSee('data-flight-step="search"', false)
            ->assertSee('data-flight-step="select"', false)
            ->assertSee('data-flight-step="travellers"', false)
            ->assertSee('data-flight-step="review"', false)
            ->assertSee('data-flight-step="confirmation"', false)
            ->assertSee('css/egh-flight.css', false)
            ->assertSee('js/egh-flight-results.js', false);
    }

    public function test_traveller_step_script_rearranges_the_rendered_step_only(): void
    {
        $script = $this->script();

        foreach (
            [
                'enhanceTravellerStep',
                'syncJourneyStep',
                'markJourneyStep',
                'flight-traveler-review-layout',
                'flight-traveler-review-main',
                'flight-traveler-summary',
                'flight-traveler-summary-note',
                'data-flight-journey-steps',
                'data-flight-step',
                'aria-current',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $script,
            );
        }

        /*
         * The layout is rebuilt from the pieces the booking script rendered,
         * so it has to be able to recognise an already-finished step. Without
         * that marker every mutation would rebuild the same panel again.
         */
        $this->assertStringContainsString(
            "flightTravellerStep === 'ready'",
            $script,
        );

        /*
         * Traveller cards, their fields and the counts behind them belong to
         * the booking script and the server-side validation. This file must
         * never touch them.
         */
        $this->assertStringNotContainsString(
            'flight-traveler-input',
            $script,
        );

        $this->assertStringNotContainsString(
            'flight-traveler-card',
            $script,
        );

        $this->assertStringNotContainsString(
            'data-flight-traveler-field',
            $script,
        );
    }

    public function test_traveller_step_script_cannot_reach_a_provider_or_inject_markup(): void
    {
        $script = $this->script();

        $this->assertStringNotContainsString('fetch(', $script);
        $this->assertStringNotContainsString('XMLHttpRequest', $script);
        $this->assertStringNotContainsString('.innerHTML', $script);
        $this->assertStringNotContainsString('insertAdjacentHTML', $script);
        $this->assertStringNotContainsString('localStorage', $script);
        $this->assertStringNotContainsString('sessionStorage', $script);

        /*
         * Every node it builds is created and filled through safe DOM calls.
         */
        $this->assertStringContainsString(
            'document.createElement(',
            $script,
        );

        $this->assertStringContainsString(
            '.textContent =',
            $script,
        );

        $this->assertStringContainsString(
            '.appendChild(',
            $script,
        );
    }

    public function test_traveller_step_copy_makes_no_booking_claim(): void
    {
        $script = $this->script();

        /*
         * The summary has to say where the counts and the total come from,
         * rather than implying they can be changed or re-priced here.
         */
        $this->assertStringContainsString(
            'cannot be changed on this step',
            $script,
        );

        $this->assertStringContainsString(
            'server-stored',
            $script,
        );

        foreach (
            [
                'booking confirmed',
                'payment successful',
                'Payment successful',
                'ticket issued',
                'e-ticket',
                'reservation confirmed',
            ] as $forbidden
        ) {
            $this->assertStringNotContainsString(
                $forbidden,
                $script,
            );
        }
    }

    public function test_traveller_step_stylesheet_is_page_scoped_and_two_column(): void
    {
        $stylesheet = $this->stylesheet();

        foreach (
            [
                'TRAVELLER DETAILS (SCREEN 3)',
                '.flight-traveler-review-layout',
                '.flight-traveler-review-main',
                '.flight-traveler-summary',
                '.flight-traveler-summary-note',
                'grid-template-columns: minmax(0, 1fr) 320px;',
                'position: sticky;',
                '@media (max-width: 980px) {',
            ] as $marker
        ) {
            $this->assertStringContainsString(
                $marker,
                $stylesheet,
            );
        }

        /*
         * The booking script labels its fields with two different classes:
         * .flight-traveler-field-label for title, names and date of birth, and
         * .flight-traveler-label for gender, email and phone. This sheet has
         * to restyle both, or three labels keep the older bundled type scale
         * while the other four move, and the field row no longer matches.
         */
        $this->assertStringContainsString(
            '.flight-traveler-field-label',
            $stylesheet,
        );

        $this->assertStringContainsString(
            '.flight-traveler-label',
            $stylesheet,
        );

        /*
         * The panel only sticks while it sits in its own column. The bundled
         * sheet hides overflow on the step, which would trap the panel in a
         * scroll container, so this sheet has to release it.
         */
        $this->assertStringContainsString(
            'overflow: visible;',
            $stylesheet,
        );

        /*
         * Page-scoped sheet: nothing in it may reach another screen.
         */
        foreach (
            [
                '.hotel-',
                '.egha-',
                '.site-header',
                '.admin-',
            ] as $foreign
        ) {
            $this->assertStringNotContainsString(
                $foreign,
                $stylesheet,
            );
        }
    }

    private function script(): string
    {
        $script = file_get_contents(
            public_path('js/egh-flight-results.js')
        );

        $this->assertIsString($script);

        return $script;
    }

    private function stylesheet(): string
    {
        $stylesheet = file_get_contents(
            public_path('css/egh-flight.css')
        );

        $this->assertIsString($stylesheet);

        return $stylesheet;
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $customer->assignRole('customer');

        return $customer;
    }
}
