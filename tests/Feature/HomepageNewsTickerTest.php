<?php

namespace Tests\Feature;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageNewsTickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_the_news_ticker_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('egho-news-ticker', false)
            ->assertSee('Latest News')
            ->assertSee('Welcome to Eagle Global Hub LTD')
            ->assertSee('css/egh-news-ticker.css', false);
    }

    public function test_homepage_news_ticker_uses_existing_homepage_settings(): void
    {
        $settings = app(SettingService::class);
        $settings->set('homepage', 'news_label', 'Notice', 'string', true);
        $settings->set('homepage', 'news_text', 'Visa desk hours have been updated.', 'string', true);
        $settings->set('homepage', 'news_url', 'https://example.com/update', 'string', true);
        $settings->set('homepage', 'news_link_label', 'Details', 'string', true);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Notice')
            ->assertSee('Visa desk hours have been updated.')
            ->assertSee('https://example.com/update', false)
            ->assertSee('Details');
    }

    public function test_homepage_news_ticker_can_be_disabled_from_settings(): void
    {
        app(SettingService::class)->set('homepage', 'news_enabled', '0', 'string', true);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('egho-news-ticker', false);
    }
}
