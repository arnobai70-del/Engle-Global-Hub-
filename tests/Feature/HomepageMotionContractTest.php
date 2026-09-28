<?php

namespace Tests\Feature;

use Tests\TestCase;

final class HomepageMotionContractTest extends TestCase
{
    public function test_homepage_wires_continuous_news_and_destination_motion(): void
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->getContent();

        $this->assertIsString($html);
        $this->assertStringContainsString('egho-news-ticker-track', $html);
        $this->assertStringContainsString('data-egho-autoplay="continuous"', $html);
        $this->assertStringContainsString('data-egho-rail-track', $html);

        $script = file_get_contents(public_path('js/egh-home.js'));
        $this->assertIsString($script);
        $this->assertStringContainsString('var initTicker = function', $script);
        $this->assertStringContainsString("clone.setAttribute('aria-hidden', 'true')", $script);
        $this->assertStringContainsString('viewport.scrollLeft += speed * (elapsed / 1000)', $script);
        $this->assertStringContainsString('Math.max(58, configuredSpeed)', $script);
        $this->assertStringContainsString('track.scrollLeft += speed * (elapsed / 1000)', $script);
    }

    public function test_motion_styles_keep_ticker_flowing_and_controls_polished(): void
    {
        $ticker = file_get_contents(public_path('css/egh-news-ticker.css'));
        $effects = file_get_contents(public_path('css/egh-home-effects.css'));

        $this->assertIsString($ticker);
        $this->assertIsString($effects);
        $this->assertStringContainsString('.egho-news-ticker.is-ticker-enhanced', $ticker);
        $this->assertStringContainsString('@keyframes egho-news-scroll', $ticker);
        $this->assertStringNotContainsString('.egho-news-ticker:hover .egho-news-ticker-track', $ticker);
        $this->assertStringContainsString('.egho-rail-head[data-egho-rail-nav]', $effects);
        $this->assertStringContainsString('@keyframes eghRailButtonPress', $effects);
    }
}
