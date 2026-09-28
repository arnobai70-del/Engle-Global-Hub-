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
        $this->assertStringContainsString('data-egho-interval="2600"', $html);
        $this->assertStringContainsString('data-egho-rail-track', $html);
        $this->assertStringContainsString('data-egho-rail-prev', $html);
        $this->assertStringContainsString('data-egho-rail-next', $html);

        $script = file_get_contents(public_path('js/egh-home.js'));
        $this->assertIsString($script);
        $this->assertStringNotContainsString('var initTicker = function', $script);
        $this->assertStringContainsString("rail.getAttribute('data-egho-autoplay') === 'step'", $script);
        $this->assertStringContainsString("rail.getAttribute('data-egho-interval') || '2600'", $script);
        $this->assertStringContainsString('var duration = 760;', $script);
        $this->assertStringContainsString('moveOne(1, true);', $script);
        $this->assertStringContainsString("clone.classList.add('is-rail-clone')", $script);
        $this->assertStringContainsString('window.setTimeout(function ()', $script);
        $this->assertStringContainsString('track.scrollLeft = start + (distance * ease(progress));', $script);
    }

    public function test_motion_styles_keep_ticker_flowing_and_controls_polished(): void
    {
        $ticker = file_get_contents(public_path('css/egh-news-ticker.css'));
        $effects = file_get_contents(public_path('css/egh-home-effects.css'));

        $this->assertIsString($ticker);
        $this->assertIsString($effects);
        $this->assertStringContainsString('@keyframes egho-news-marquee', $ticker);
        $this->assertStringContainsString('animation: egho-news-marquee 32s linear infinite;', $ticker);
        $this->assertStringContainsString('.egho-news-ticker-group', $ticker);
        $this->assertStringNotContainsString('.egho-news-ticker:hover .egho-news-ticker-track', $ticker);
        $this->assertStringContainsString('.egho-rail-head[data-egho-rail-nav]', $effects);
        $this->assertStringContainsString('@keyframes eghRailArrowNext', $effects);
        $this->assertStringContainsString('@keyframes eghRailArrowPrev', $effects);
        $this->assertStringContainsString('background: linear-gradient(135deg, #0a386c 0%, #0b4d8d 100%);', $effects);
    }
}
