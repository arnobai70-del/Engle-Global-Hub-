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

    public function test_homepage_follows_the_singapore_consultancy_section_order(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('Your Gateway to Study in Singapore', $html);
        $this->assertStringContainsString('Singapore Student Consultancy', $html);
        $this->assertOrder('sg-hero', 'sg-tiles', $html);
        $this->assertOrder('sg-tiles', 'Complete Student Consultancy for', $html);
        $this->assertOrder('Complete Student Consultancy for', 'Our Complete Services', $html);
        $this->assertOrder('Our Complete Services', 'Your journey to Singapore, step by step', $html);
        $this->assertOrder('Your journey to Singapore, step by step', 'Visa &amp; Travel Services', $html);
    }

    public function test_homepage_renders_the_seven_service_tiles_with_study_featured(): void
    {
        $html = $this->home();

        $this->assertSame(6, substr_count($html, 'class="sg-tile"'));
        $this->assertSame(1, substr_count($html, 'sg-tile is-featured'));
        foreach (['Study in Singapore', 'Student&#039;s Pass (ICA)', 'Visa Services', 'Flight Services', 'Hotels &amp; Homes', 'Tour Packages', 'Explore Bangladesh'] as $tile) {
            $this->assertStringContainsString($tile, $html);
        }
    }

    public function test_homepage_lists_the_six_consultancy_services_and_secondary_services(): void
    {
        $html = $this->home();

        foreach (['Admission Support', 'Student&#039;s Pass (ICA) Support', 'Documents Support', 'Air Ticket Booking', 'Accommodation', 'Arrival Support'] as $service) {
            $this->assertStringContainsString('<h3>'.$service.'</h3>', $html);
        }
        foreach (['Work Visa', 'Flights', 'Offers &amp; Promotions'] as $secondary) {
            $this->assertStringContainsString('<h3>'.$secondary.'</h3>', $html);
        }
        $this->assertStringContainsString(route('consultancy.singapore').'#consultation', $html);
    }

    public function test_homepage_shows_only_published_guides_and_destinations(): void
    {
        $html = $this->home();

        $this->assertStringNotContainsString('sg-guide-list', $html, 'no guide is published in this test, so none may be listed');
        $this->assertStringNotContainsString('sg-photo-card', $html, 'no destination is published in this test, so none may be listed');
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

    public function test_homepage_loads_the_shared_design_sheet(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('css/egh-sg.css?v=', $html);
        $this->assertStringContainsString('css/egh-chrome.css?v=', $html);
        $this->assertStringContainsString('css/egh-news-ticker.css?v=', $html);
    }

    public function test_homepage_chrome_carries_the_reference_navigation_and_footer(): void
    {
        $html = $this->home();

        foreach (['Study in Singapore', 'Visa Services', 'Travel Services', 'Explore Bangladesh', 'About'] as $menu) {
            $this->assertStringContainsString('<summary>'.$menu.'</summary>', $html);
        }
        $this->assertStringContainsString('Apply Now', $html);
        $this->assertStringContainsString('data-sg-burger', $html);

        foreach (['Quick Links', 'Study in Singapore', 'Our Services', 'Contact Info'] as $column) {
            $this->assertStringContainsString('<h3>'.$column.'</h3>', $html);
        }

        $this->assertStringContainsString('Secure account &amp; booking steps', $html);
    }
}
