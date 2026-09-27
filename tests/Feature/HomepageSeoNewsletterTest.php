<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageSeoNewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_core_seo_metadata_and_schema(): void
    {
        $response = $this->get(route('home'))->assertOk();
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('TravelAgency', false);
    }

    public function test_sitemap_is_valid_public_xml(): void
    {
        $this->get(route('sitemap'))->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('home'), false);
    }

    public function test_newsletter_subscription_is_idempotent_for_same_email(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => 'Traveler@Example.com'])->assertRedirect();
        $this->post(route('newsletter.subscribe'), ['email' => 'traveler@example.com'])->assertRedirect();
        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertSame('traveler@example.com', NewsletterSubscriber::query()->firstOrFail()->email);
    }
}
