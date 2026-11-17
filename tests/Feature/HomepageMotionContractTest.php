<?php

namespace Tests\Feature;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomepageMotionContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_wires_the_seamless_news_ticker(): void
    {
        app(SettingService::class)->set('homepage', 'news_text', 'January intake applications are open.', 'string', true);
        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);
        $this->assertStringContainsString('egho-news-ticker-track', $html);
        $this->assertStringContainsString('--egho-news-duration: 20s', $html);
        $this->assertSame(2, substr_count($html, 'class="egho-news-ticker-group"'));
    }

    public function test_motion_styles_keep_ticker_and_destination_motion_active(): void
    {
        $ticker = file_get_contents(public_path('css/egh-news-ticker.css'));
        $effects = file_get_contents(public_path('css/egh-home-effects.css'));

        $this->assertIsString($ticker);
        $this->assertIsString($effects);
        $this->assertStringContainsString('@keyframes egho-news-marquee', $ticker);
        $this->assertStringContainsString('animation: egho-news-marquee var(--egho-news-duration, 20s) linear infinite !important;', $ticker);
        $this->assertStringContainsString('animation-play-state: running !important;', $ticker);
        $this->assertStringContainsString('.egho-news-ticker-group', $ticker);
        $this->assertStringNotContainsString('prefers-reduced-motion', $ticker);
        $this->assertStringNotContainsString('prefers-reduced-motion', $effects);
        $this->assertStringContainsString('.egho-rail.is-motion-ready', $effects);
        $this->assertStringContainsString('@keyframes eghRailArrowNext', $effects);
        $this->assertStringContainsString('@keyframes eghRailArrowPrev', $effects);
    }

    public function test_public_site_has_consistent_button_hover_press_and_focus_feedback(): void
    {
        $polish = file_get_contents(public_path('css/egh-final-polish.css'));

        $this->assertIsString($polish);
        $this->assertStringContainsString('Public interactions', $polish);
        $this->assertStringContainsString('.site-button-primary', $polish);
        $this->assertStringContainsString('.egho-submit', $polish);
        $this->assertStringContainsString('.egho-panel-cta', $polish);
        $this->assertStringContainsString('.egho-promo-cta', $polish);
        $this->assertStringContainsString('.egho-nav-more > summary', $polish);
        $this->assertStringContainsString('translateY(-2px) scale(1.015)', $polish);
        $this->assertStringContainsString('scale(.965)', $polish);
        $this->assertStringContainsString(':focus-visible', $polish);
    }
}
