<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One footer call-to-action band per page type, chosen in App\Services\Content\PageType.
 */
class FooterCtaTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_study_page_shows_the_study_band(): void
    {
        $this->get(route('services.show', 'student-visa'))->assertOk()
            ->assertSee('data-footer-cta="study"', false)
            ->assertSee('Ready to study in Malaysia?')
            ->assertDontSee('data-footer-cta="travel"', false);
    }

    public function test_a_travel_page_shows_the_travel_band(): void
    {
        $this->get(route('travel-services.index'))->assertOk()
            ->assertSee('data-footer-cta="travel"', false)
            ->assertSee('Planning a trip?')
            ->assertDontSee('Ready to study in Malaysia?');
    }

    public function test_a_neutral_page_offers_both_study_and_travel(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('data-footer-cta="neutral"', false)
            ->assertSee('Talk to our team')
            ->assertSee('Study abroad')
            ->assertSee('Travel &amp; visa', false)
            ->assertSee('href="'.route('travel-services.index').'"', false)
            ->assertDontSee('Ready to study in Malaysia?');
    }

    public function test_explore_bangladesh_shows_the_trip_band_and_the_home_page_shows_none(): void
    {
        $this->get(route('explore-bangladesh.index'))->assertOk()
            ->assertSee('data-footer-cta="explore"', false)
            ->assertSee('Planning a trip to Bangladesh?');

        $this->get(route('home'))->assertOk()->assertDontSee('data-footer-cta=', false);
    }
}
