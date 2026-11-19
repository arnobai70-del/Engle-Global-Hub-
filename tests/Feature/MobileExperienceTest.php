<?php

namespace Tests\Feature;

use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MobileExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_page_has_the_mobile_call_whatsapp_apply_bar(): void
    {
        app(SettingService::class)->set('contact', 'phone', '01953626481', 'string', true);

        foreach (['home', 'consultancy.singapore', 'about', 'support', 'privacy', 'why-us'] as $page) {
            $html = (string) $this->get(route($page))->assertOk()->getContent();
            $this->assertStringContainsString('class="v2-mobile-bar"', $html, $page);
            $this->assertStringContainsString('href="tel:+8801953626481"', $html, $page);
            $this->assertStringContainsString('class="is-whatsapp" href="https://wa.me/8801953626481', $html, $page);
            $this->assertStringContainsString('class="is-apply" href="'.route('consultancy.malaysia').'#apply"', $html, $page);
        }
    }

    public function test_mobile_stylesheet_keeps_tap_targets_at_least_44px_and_hides_duplicate_whatsapp(): void
    {
        $css = (string) file_get_contents(public_path('css/egh-v2.css'));

        $this->assertStringContainsString('Mobile tap targets: at least 44px', $css);
        foreach (['.sg-dd-menu a', '.sg-footer li a', '.v2-crumbs a', '.egho-auth-submit'] as $selector) {
            $this->assertStringContainsString($selector, $css);
        }
        $this->assertMatchesRegularExpression('/\.v2-mobile-bar a \{[^}]*min-height: 60px/', $css);
        $this->assertMatchesRegularExpression('/@media \(max-width: 760px\) \{[^@]*\.sg-wa-float \{ display: none; \}/', $css);
        $this->assertStringContainsString('outline: 3px solid var(--focus)', $css, 'visible focus ring');
    }
}
