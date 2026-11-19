<?php

namespace Tests\Feature;

use App\Models\HomepageBlock;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageNewsTickerTest extends TestCase
{
    use RefreshDatabase;

    private function enable(bool $enabled = true): void
    {
        app(SettingService::class)->set('homepage', 'news_enabled', $enabled ? '1' : '0', 'string', true);
    }

    /** @param array<string, mixed> $attributes */
    private function headline(array $attributes = []): HomepageBlock
    {
        $item = HomepageBlock::query()->create(['section' => NewsTicker::SECTION, 'title' => 'January intake applications are open.', 'is_active' => true, 'sort_order' => 10, ...$attributes]);
        app(NewsTicker::class)->forget();

        return $item;
    }

    public function test_the_ticker_shows_only_when_enabled_with_an_active_headline(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('data-news-ticker', false)->assertDontSee('has-news-ticker', false);

        $this->enable();
        $this->get(route('home'))->assertDontSee('data-news-ticker', false);

        $this->headline(['url' => '/study-in-malaysia', 'cta_label' => 'Intake']);
        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('<section class="egho-news-ticker" aria-label="Latest updates" data-news-ticker data-speed="55"', $html);
        $this->assertStringContainsString('class="v2-hero v2-hero-photo  has-news-ticker', $html);
        $this->assertStringContainsString('href="/study-in-malaysia"', $html);
        $this->assertStringContainsString('<span class="egho-news-ticker-badge">Intake</span>', $html);
        $this->assertSame(2, substr_count($html, 'class="egho-news-ticker-group"'));
        $this->assertSame(1, substr_count($html, 'aria-hidden="true" data-news-duplicate'), 'the loop copy is hidden from screen readers');
        $this->assertMatchesRegularExpression('#href="/study-in-malaysia"\s+tabindex="-1"#', $html, 'links in the loop copy are not focusable');
        $this->assertSame(1, preg_match_all('#href="/study-in-malaysia"\s*>#', $html), 'the real list keeps one focusable link');
        $this->assertStringContainsString('aria-label="Pause headlines" data-news-toggle hidden', $html);

        $this->enable(false);
        $this->get(route('home'))->assertDontSee('data-news-ticker', false);
    }

    public function test_draft_expired_and_future_headlines_never_render(): void
    {
        $this->enable();
        $this->headline(['title' => 'Draft headline', 'is_active' => false]);
        $this->headline(['title' => 'Expired headline', 'ends_at' => now()->subMinute()]);
        $this->headline(['title' => 'Future headline', 'starts_at' => now()->addDay()]);

        $this->get(route('home'))->assertOk()->assertDontSee('data-news-ticker', false)
            ->assertDontSee('Draft headline')->assertDontSee('Expired headline')->assertDontSee('Future headline');

        $this->headline(['title' => 'Current headline', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $this->get(route('home'))->assertSee('Current headline')->assertDontSee('Expired headline')->assertDontSee('Future headline');
    }

    public function test_bengali_falls_back_to_english_and_links_open_safely(): void
    {
        $this->enable();
        app(SettingService::class)->set('homepage', 'news_label_bn', 'সর্বশেষ', 'string', true);
        $this->headline(['title' => 'English only headline']);
        $this->headline(['title' => 'Offer headline', 'subtitle' => 'অফারের খবর', 'url' => 'https://example.com/offer', 'sort_order' => 20]);

        $this->get(route('home', ['lang' => 'bn']))->assertOk()
            ->assertSee('English only headline')
            ->assertSee('অফারের খবর')->assertDontSee('Offer headline')
            ->assertSee('সর্বশেষ')
            ->assertSee('href="https://example.com/offer"  target="_blank" rel="noopener noreferrer"', false);
    }

    public function test_headline_text_is_escaped(): void
    {
        $this->enable();
        $this->headline(['title' => '<script>alert(1)</script> <b>Bold</b>', 'cta_label' => '<i>New</i>']);

        $this->get(route('home'))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>Bold</b>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;i&gt;New&lt;/i&gt;', false);
    }

    public function test_the_speed_setting_reaches_the_page(): void
    {
        $this->enable();
        app(SettingService::class)->set('homepage', 'news_speed', 'fast', 'string', true);
        $this->headline();

        $this->get(route('home'))->assertSee('data-speed="80"', false);
        $this->assertSame(35, NewsTicker::SPEEDS['slow']);
    }

    public function test_active_headlines_are_cached_briefly(): void
    {
        $this->enable();
        $this->headline();
        $this->get(route('home'))->assertSee('January intake applications are open.');

        $this->assertTrue(Cache::has(NewsTicker::CACHE_KEY));
    }
}
