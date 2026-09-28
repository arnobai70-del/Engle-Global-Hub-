<?php

namespace Tests\Feature;

use Tests\TestCase;

final class HomepageMotionContractTest extends TestCase
{
    public function test_homepage_wires_seamless_news_and_timed_destination_motion(): void
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);
        $this->assertStringContainsString('egho-news-ticker-track', $html);
        $this->assertSame(2, substr_count($html, 'class="egho-news-ticker-group"'));
        $this->assertStringContainsString('data-egho-autoplay="step"', $html);
        $this->assertStringContainsString('data-egho-interval="2200"', $html);
        $this->assertStringContainsString('data-egho-rail-track', $html);
        $this->assertStringContainsString('data-egho-rail-prev', $html);
        $this->assertStringContainsString('data-egho-rail-next', $html);

        $script = file_get_contents(public_path('js/egh-home.js'));
        $this->assertIsString($script);
        $this->assertStringNotContainsString('prefers-reduced-motion', $script);
        $this->assertStringContainsString("rail.getAttribute('data-egho-autoplay') === 'step'", $script);
        $this->assertStringContainsString("rail.getAttribute('data-egho-interval') || '2200'", $script);
        $this->assertStringContainsString('var duration = 700;', $script);
        $this->assertStringContainsString('scheduleAutoplay(900);', $script);
        $this->assertStringContainsString('moveOne(1, true);', $script);
        $this->assertStringContainsString("clone.classList.add('is-rail-clone')", $script);
        $this->assertStringContainsString("rail.classList.add('is-motion-ready')", $script);
        $this->assertStringContainsString('track.scrollLeft = start + (distance * ease(progress));', $script);
    }

    public function test_motion_styles_keep_ticker_and_destination_motion_active(): void
    {
        $ticker = file_get_contents(public_path('css/egh-news-ticker.css'));
        $effects = file_get_contents(public_path('css/egh-home-effects.css'));

        $this->assertIsString($ticker);
        $this->assertIsString($effects);
        $this->assertStringContainsString('@keyframes egho-news-marquee', $ticker);
        $this->assertStringContainsString('animation: egho-news-marquee 20s linear infinite !important;', $ticker);
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
