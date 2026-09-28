<?php

namespace Tests\Feature\Analytics;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaPixelTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_pixel_is_disabled_by_default(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false)
            ->assertDontSee("fbq('track', 'PageView')", false);
    }

    public function test_enabled_valid_meta_pixel_renders_on_public_and_auth_pages(): void
    {
        $settings = app(SettingService::class);
        $settings->set('analytics', 'meta_pixel_id', '123456789012345', 'string', true);
        $settings->set('analytics', 'meta_pixel_enabled', true, 'boolean', true);

        foreach ([route('terms'), route('login')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('https://connect.facebook.net/en_US/fbevents.js', false)
                ->assertSee("fbq('init', '123456789012345')", false)
                ->assertSee("fbq('track', 'PageView')", false)
                ->assertSee('https://www.facebook.com/tr?id=123456789012345', false);
        }
    }

    public function test_invalid_stored_meta_pixel_id_never_renders_tracking_code(): void
    {
        $settings = app(SettingService::class);
        $settings->set('analytics', 'meta_pixel_id', 'not-a-pixel-id', 'string', true);
        $settings->set('analytics', 'meta_pixel_enabled', true, 'boolean', true);

        $this->get(route('terms'))
            ->assertOk()
            ->assertDontSee('connect.facebook.net/en_US/fbevents.js', false)
            ->assertDontSee('not-a-pixel-id', false);
    }
}
