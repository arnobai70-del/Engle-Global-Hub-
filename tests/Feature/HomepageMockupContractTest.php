<?php

namespace Tests\Feature;

use Tests\TestCase;

final class HomepageMockupContractTest extends TestCase
{
    private function home(): string
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);
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
        $this->assertOrder('Why Choose Eagle Global Hub?', 'Download Our Mobile App', $html);
        $this->assertOrder('Download Our Mobile App', 'Make Your Next Journey Amazing', $html);
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
            $this->assertStringContainsString($tile, $html, 'the services grid is missing '.$tile);
        }

        $this->assertSame(5, substr_count($html, 'egho-tile-status is-planned'));
        $this->assertStringContainsString('Live Provider', $html);
        $this->assertStringContainsString('Demo Preview', $html);
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
        $this->assertSame(6, substr_count($html, 'egho-why-icon'));
    }

    public function test_homepage_publishes_only_the_official_contact_details_and_no_fake_promo_code(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('info@eagleglobalhub.com', $html);
        $this->assertStringContainsString('01953626481', $html);
        $this->assertStringContainsString('Mysha Chowdhury Tower', $html);
        $this->assertStringContainsString('Pragati Sharani', $html);
        $this->assertStringNotContainsString('support@', $html);
        $this->assertStringNotContainsString('EGHFLY', $html);
    }

    public function test_customer_comments_stay_out_of_every_environment_but_local(): void
    {
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

        $this->assertSame(1, substr_count($html, 'egho-nav-more'));

        foreach ([
            'Quick Links',
            'Our Services',
            'Support',
            'Contact Info',
        ] as $column) {
            $this->assertStringContainsString('<h3>'.$column.'</h3>', $html);
        }

        $this->assertStringContainsString('Secure account &amp; booking steps', $html);
        $this->assertStringContainsString('Provider credentials stay server-side', $html);
    }
}
