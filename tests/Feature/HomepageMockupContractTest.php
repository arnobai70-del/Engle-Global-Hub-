<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Homepage panel contract.
 *
 * The homepage was rebuilt to follow the approved mockup panel section by
 * section. These assertions pin the parts a later change could quietly undo:
 * the order of the sections, the number of cards in each grid, the fact that
 * the page-scoped sheet and its rail script ship with a cache-busting version,
 * and — most importantly — the places where this website deliberately does not
 * copy the panel, because the panel promises something the application cannot
 * honour.
 */
final class HomepageMockupContractTest extends TestCase
{
    /**
     * Fetch the homepage and assert the panel-level basics.
     */
    private function home(): string
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);

        /* The panel has one h1: the hero headline. */
        $this->assertSame(
            1,
            substr_count($html, '<h1'),
            'the homepage renders '.substr_count($html, '<h1').' <h1> elements',
        );

        $this->assertStringNotContainsString(
            'href="#"',
            $html,
            'the homepage contains a link that goes nowhere',
        );

        return $html;
    }

    /**
     * Assert that one marker appears before another in the rendered page.
     */
    private function assertOrder(string $first, string $second, string $html): void
    {
        $firstAt = strpos($html, $first);
        $secondAt = strpos($html, $second);

        $this->assertNotFalse($firstAt, 'the homepage is missing '.$first);
        $this->assertNotFalse($secondAt, 'the homepage is missing '.$second);

        $this->assertLessThan(
            $secondAt,
            $firstAt,
            $second.' renders before '.$first,
        );
    }

    public function test_homepage_follows_the_approved_panel_section_order(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('Travel the World with', $html);

        $this->assertOrder('egho-hero', 'egho-search-card', $html);
        $this->assertOrder('egho-search-card', 'egho-assurances', $html);
        $this->assertOrder('egho-assurances', 'egho-promos', $html);
        $this->assertOrder('egho-promos', 'Our Services', $html);
        $this->assertOrder('Our Services', 'Popular Destinations', $html);
        $this->assertOrder('Popular Destinations', 'egho-panels', $html);
        $this->assertOrder('egho-panels', 'Why Choose Eagle Global Hub?', $html);
        $this->assertOrder(
            'Why Choose Eagle Global Hub?',
            'Download Our Mobile App',
            $html,
        );
        $this->assertOrder(
            'Download Our Mobile App',
            'Make Your Next Journey Amazing',
            $html,
        );
    }

    public function test_homepage_renders_the_panels_ten_service_tiles(): void
    {
        $html = $this->home();

        $this->assertSame(10, substr_count($html, 'class="egho-tile"'));

        foreach ([
            'Flights',
            'Hotels',
            'Tours &amp; Activities',
            'Visa Assistance',
            'Work Visa Processing',
            'Holiday Packages',
            'Trains',
            'Buses',
            'Cabs',
            'Travel Insurance',
        ] as $tile) {
            $this->assertStringContainsString(
                $tile,
                $html,
                'the services grid is missing '.$tile,
            );
        }

        /*
         * Five of the ten tiles describe products this application does not
         * sell. They must say so rather than render as dead links.
         */
        $this->assertSame(5, substr_count($html, 'egho-tile-status is-planned'));

        /*
         * The remaining five map to the travel service registry and carry its
         * own availability wording.
         */
        $this->assertStringContainsString('Available', $html);
        $this->assertStringContainsString('Not Configured', $html);
    }

    public function test_homepage_renders_the_panels_eight_destination_cards(): void
    {
        $html = $this->home();

        $this->assertSame(8, substr_count($html, 'egho-destination-copy'));

        foreach ([
            'Dubai',
            'Bali',
            'Bangkok',
            'Singapore',
            'Maldives',
            'London',
            'Istanbul',
            'Paris',
        ] as $destination) {
            $this->assertStringContainsString($destination, $html);
        }

        /*
         * The strip is a rail driven by the page script, so its controls are
         * rendered hidden until the script has confirmed there is something to
         * scroll.
         */
        $this->assertStringContainsString('data-egho-rail-nav hidden', $html);
        $this->assertStringContainsString('data-egho-rail-track', $html);
    }

    public function test_homepage_keeps_the_five_statement_assurance_strip(): void
    {
        $html = $this->home();

        $this->assertSame(5, substr_count($html, 'egho-assurance-copy'));

        foreach ([
            'Compare fares',
            'Live revalidation',
            'Secure account steps',
            'Status you can check',
            'Honest availability',
        ] as $statement) {
            $this->assertStringContainsString($statement, $html);
        }
    }

    public function test_homepage_renders_the_promotions_and_service_panels(): void
    {
        $html = $this->home();

        $this->assertSame(2, substr_count($html, 'class="egho-promo"'));
        $this->assertSame(1, substr_count($html, 'egho-promo is-workvisa'));
        $this->assertSame(3, substr_count($html, 'class="egho-panel '));

        $this->assertStringContainsString('Visa Services', $html);
        $this->assertStringContainsString('Work Visa Processing', $html);
        $this->assertStringContainsString('Holiday Packages', $html);

        /* Six reasons, six icons. */
        $this->assertSame(6, substr_count($html, 'egho-why-icon'));
    }

    public function test_homepage_never_publishes_invented_contact_details_or_promo_codes(): void
    {
        $html = $this->home();

        /*
         * The panel's top bar and footer carry a support phone number, an email
         * address, a postal address and a discount code. None of them is
         * configured for this website, so none of them may be rendered — the
         * project rule is that contact details are never fabricated.
         */
        foreach ([
            'support@',
            '+880',
            'mailto:',
            'EGHFLY',
            'Dhaka',
        ] as $invented) {
            $this->assertStringNotContainsString(
                $invented,
                $html,
                'the homepage publishes an invented detail: '.$invented,
            );
        }

        $this->assertStringContainsString(
            'Official contact channels are not configured yet',
            $html,
        );
    }

    public function test_customer_comments_stay_out_of_every_environment_but_local(): void
    {
        /*
         * This application stores no review data, so the panel's customer
         * quotes are sample content: they may only render in the local
         * development environment, and they must be badged when they do.
         */
        $this->assertFalse(app()->environment('local'));

        $html = $this->home();

        $this->assertStringNotContainsString('What Our Customers Say', $html);
        $this->assertStringNotContainsString('egho-sample-badge', $html);
        $this->assertStringNotContainsString('egho-voice', $html);
    }

    public function test_homepage_makes_no_booking_payment_or_rating_claim(): void
    {
        $html = $this->home();

        foreach ([
            'booking confirmed',
            'payment successful',
            'Payment Successful',
            'Flight Booked',
            'Trusted by Thousands',
            'Best Price Guarantee',
            '24/7 Support',
            'star',
        ] as $claim) {
            $this->assertStringNotContainsString(
                $claim,
                $html,
                'the homepage makes an unsupported claim: '.$claim,
            );
        }
    }

    public function test_homepage_loads_its_page_scoped_sheet_and_rail_script(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('css/egh-home.css?v=', $html);
        $this->assertStringContainsString('css/egh-chrome.css?v=', $html);
        $this->assertStringContainsString('js/egh-home.js?v=', $html);

        /*
         * The rail script only ever moves a scroll position: every card is in
         * the document, so nothing may be requested, built or replaced.
         */
        $script = file_get_contents(public_path('js/egh-home.js'));

        $this->assertIsString($script);

        foreach (['fetch(', 'XMLHttpRequest', 'innerHTML', 'insertAdjacent'] as $banned) {
            $this->assertStringNotContainsString(
                $banned,
                $script,
                'the homepage script performs a forbidden operation: '.$banned,
            );
        }
    }

    public function test_homepage_chrome_carries_the_mockup_navigation_and_footer(): void
    {
        $html = $this->home();

        /* Pill navigation with the panel's grid "More" menu. */
        $this->assertSame(1, substr_count($html, 'egho-nav-more'));

        /* Five footer columns, as in the panel. */
        foreach ([
            'Quick Links',
            'Our Services',
            'Support',
            'Contact Info',
        ] as $column) {
            $this->assertStringContainsString('<h3>'.$column.'</h3>', $html);
        }

        /* The payment strip states what is true instead of card-brand marks. */
        $this->assertStringContainsString('Secure account &amp; booking steps', $html);
        $this->assertStringContainsString(
            'Provider credentials stay server-side',
            $html,
        );
    }
}
