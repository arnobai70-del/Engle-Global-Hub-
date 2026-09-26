<?php

namespace Tests\Feature\Flight;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Screen 2 contract: the flight result chrome and the script behind it.
 *
 * The point of these assertions is that the result page stays a presentation
 * layer. Every filter and sort control has to ship disabled, the script has to
 * narrow only the offers that were already returned, and it must never reach
 * for a provider or drop markup in through `innerHTML`.
 */
final class FlightResultsPageUiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(
            RolePermissionSeeder::class
        );
    }

    public function test_result_page_exposes_summary_count_and_filter_controls(): void
    {
        $response = $this
            ->actingAs($this->customer())
            ->get(route('flights.index'));

        $response
            ->assertOk()
            ->assertSee('data-flight-summary', false)
            ->assertSee('data-flight-sort', false)
            ->assertSee('data-flight-count', false)
            ->assertSee('data-flight-filters', false)
            ->assertSee('data-flight-airline-filters', false)
            ->assertSee('data-flight-price-min', false)
            ->assertSee('data-flight-price-max', false)
            ->assertSee('data-flight-departure-min', false)
            ->assertSee('data-flight-arrival-max', false)
            ->assertSee('data-flight-filter-reset', false)
            ->assertSee('egho-flight-results', false)
            ->assertSee('css/egh-flight.css', false)
            ->assertSee('js/egh-flight-results.js', false);
    }

    public function test_every_filter_control_ships_disabled_with_an_honest_note(): void
    {
        $html = $this
            ->actingAs($this->customer())
            ->get(route('flights.index'))
            ->getContent();

        $this->assertIsString($html);

        $this->assertSame(
            3,
            substr_count($html, 'data-flight-stop-filter')
        );

        $this->assertMatchesRegularExpression(
            '/data-flight-stop-filter\s+disabled/',
            $html
        );

        foreach ([
            'data-flight-price-min',
            'data-flight-price-max',
            'data-flight-departure-min',
            'data-flight-departure-max',
            'data-flight-arrival-min',
            'data-flight-arrival-max',
        ] as $control) {
            $this->assertMatchesRegularExpression(
                '/'.$control.'[\s\S]{0,200}?disabled/',
                $html
            );
        }

        $this->assertMatchesRegularExpression(
            '/data-flight-filter-reset\s+hidden/',
            $html
        );

        $this->assertStringContainsString(
            'These filters need JavaScript.',
            $html
        );

        $this->assertStringContainsString(
            'no fare is hidden or re-ordered',
            $html
        );
    }

    public function test_result_script_filters_in_the_browser_only(): void
    {
        $script = file_get_contents(
            public_path('js/egh-flight-results.js')
        );

        $this->assertIsString($script);

        /*
         * The wording the rail switches to once the controls are live. It is
         * the promise this screen makes, so it is asserted rather than trusted.
         */
        $this->assertStringContainsString(
            'never request, add or replace a fare',
            $script
        );

        $this->assertStringContainsString(
            'Lowest total fare among the options returned by this search.',
            $script
        );

        /*
         * No provider call, no markup injection, no raw HTML: the script only
         * reads the rendered offers and toggles attributes on them.
         */
        $this->assertStringNotContainsString('fetch(', $script);
        $this->assertStringNotContainsString('XMLHttpRequest', $script);
        $this->assertStringNotContainsString('.innerHTML', $script);
        $this->assertStringNotContainsString('insertAdjacentHTML', $script);

        $this->assertStringContainsString(
            'MutationObserver',
            $script
        );

        $this->assertStringContainsString(
            'data-flight-summary-origin',
            $script
        );

        /*
         * A slider nobody has touched has to follow the result set. When it
         * kept a stale position instead, a revalidation that returned a cheaper
         * fare left that fare hidden without the visitor ever excluding it.
         */
        $this->assertStringContainsString(
            'rangesTouched',
            $script
        );

        $this->assertStringContainsString(
            '!rangesTouched.priceMin',
            $script
        );

        $this->assertStringContainsString(
            '!rangesTouched.priceMax',
            $script
        );

        $this->assertStringContainsString(
            '!rangesTouched[key]',
            $script
        );
    }

    public function test_flight_search_script_publishes_the_values_these_filters_read(): void
    {
        $javascript = file_get_contents(
            resource_path('js/app.js')
        );

        $this->assertIsString($javascript);

        $this->assertStringContainsString(
            'departure.dataset.departingAt',
            $javascript
        );

        $this->assertStringContainsString(
            'arrival.dataset.arrivingAt',
            $javascript
        );

        $this->assertStringContainsString(
            'section.dataset.stops',
            $javascript
        );

        $this->assertStringContainsString(
            'section.dataset.duration',
            $javascript
        );

        $this->assertStringContainsString(
            'flight-slice-stops',
            $javascript
        );
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
