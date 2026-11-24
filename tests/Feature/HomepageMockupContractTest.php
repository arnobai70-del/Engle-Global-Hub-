<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\HomepageBlock;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage follows DESIGN_BRIEF.md (Main mockup): hero with consultation form,
 * trust strip, admin-controlled services, how it works, clear costs, stories, FAQ, contact band.
 */
final class HomepageMockupContractTest extends TestCase
{
    use RefreshDatabase;

    private function home(): string
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'the homepage renders '.substr_count($html, '<h1').' <h1> elements');
        $this->assertStringNotContainsString('href="#"', $html, 'the homepage contains a link that goes nowhere');

        return $html;
    }

    private function assertOrder(array $markers, string $html): void
    {
        $previous = -1;
        foreach ($markers as $marker) {
            $at = strpos($html, $marker);
            $this->assertNotFalse($at, 'the homepage is missing '.$marker);
            $this->assertGreaterThan($previous, $at, $marker.' is out of order');
            $previous = $at;
        }
    }

    public function test_homepage_shows_the_consultation_form_whatsapp_and_no_technical_wording(): void
    {
        app(SettingService::class)->set('contact', 'phone', '01953626481', 'string', true);
        $html = $this->home();

        $this->assertStringContainsString('action="'.route('leads.consultation').'"', $html);
        $this->assertStringContainsString('name="study_level"', $html);
        $this->assertStringContainsString('name="preferred_intake"', $html);
        $this->assertStringContainsString('name="consent"', $html);
        $this->assertStringContainsString('https://wa.me/8801953626481', $html);
        $this->assertStringNotContainsString('WAITING FOR API', $html);
        $this->assertStringNotContainsString('Waiting for API', $html);
        $this->assertStringContainsString('<title>Study in Malaysia from Bangladesh | Eagle Global Hub</title>', $html);
    }

    public function test_homepage_follows_the_design_section_order(): void
    {
        $this->assertOrder([
            'id="hero-title"', 'id="consultation-form"', 'class="v2-trust"', 'id="services-title"',
            'id="how-it-works"', 'id="costs-title"', 'id="visit-title"',
        ], $this->home());
    }

    public function test_homepage_lists_five_default_services_with_actions_and_no_singapore_tile(): void
    {
        $html = $this->home();

        $this->assertSame(5, substr_count($html, 'class="v2-service-card"'));
        foreach (['Study in Malaysia', 'Student Pass (EMGS)', 'Visa services', 'Air tickets', 'Housing &amp; arrival'] as $service) {
            $this->assertStringContainsString('<h3>'.$service.'</h3>', $html);
        }
        $this->assertStringNotContainsString('<h3>Study in Singapore</h3>', $html);
        foreach (['Get a fare quote', 'Ask about housing', 'Ask about a visa'] as $action) {
            $this->assertStringContainsString($action, $html);
        }
        $this->assertSame(5, substr_count($html, 'class="v2-step-no"'), 'how it works has five steps');
    }

    public function test_owner_numbers_appear_only_once_entered_in_admin(): void
    {
        $html = $this->home();
        foreach (['[X]', '[YEAR]', '[NO.]', 'students guided', 'Trade licence', 'Google rating', 'SGD '] as $placeholder) {
            $this->assertStringNotContainsString($placeholder, $html, $placeholder.' must not show before the owner enters it');
        }

        $settings = app(SettingService::class);
        foreach (['students_guided' => '120', 'founded_year' => '2019', 'trade_licence_no' => 'TRAD/DNCC/123', 'my_cost_tuition' => '18,000', 'google_rating' => '4.8', 'google_review_count' => '57', 'google_reviews_url' => 'https://g.page/example'] as $key => $value) {
            $settings->set('facts', $key, $value, 'string', true);
        }

        $html = $this->home();
        $this->assertStringContainsString('120+', $html);
        $this->assertStringContainsString('years in business', $html, 'founded year is shown as years in business');
        $this->assertStringContainsString('TRAD/DNCC/123', $html);
        $this->assertStringContainsString('MYR 18,000', $html);
        $this->assertStringContainsString('Google rating 4.8 ★', $html);
    }

    public function test_news_ticker_shows_only_admin_headlines_and_never_the_old_welcome_text(): void
    {
        $settings = app(SettingService::class);
        $settings->set('homepage', 'news_enabled', '1', 'string', true);
        $settings->set('homepage', 'news_text', 'Welcome to Eagle Global Hub LTD — Travel & Visa Services.', 'string', true);
        $this->assertStringNotContainsString('data-news-ticker', $this->home());
        $this->assertStringNotContainsString('Welcome to Eagle Global Hub LTD', $this->home());

        HomepageBlock::query()->create(['section' => NewsTicker::SECTION, 'title' => 'January 2027 intake applications are open.', 'is_active' => true]);
        app(NewsTicker::class)->forget();
        $this->assertStringContainsString('January 2027 intake applications are open.', $this->home());
    }

    public function test_success_stories_appear_only_when_published(): void
    {
        $this->assertStringNotContainsString('Students who made it', $this->home());

        $story = new ContentEntry(['slug' => 'rahim', 'title' => 'Rahim, Diploma in Business', 'summary' => 'The team explained every step.', 'body' => 'Story']);
        $story->type = ContentType::SuccessStory;
        $story->status = ContentEntry::STATUS_PUBLISHED;
        $story->save();

        $this->assertStringContainsString('The team explained every step.', $this->home());
    }

    public function test_homepage_publishes_only_the_official_contact_details_and_no_fake_promo_code(): void
    {
        $html = $this->home();

        $this->assertStringContainsString('info@eagleglobalhub.com', $html);
        $this->assertStringContainsString('01953626481', $html);
        $this->assertStringContainsString('Mysha Chowdhury Tower', $html);
        $this->assertStringNotContainsString('support@', $html);
        $this->assertStringNotContainsString('EGHFLY', $html);
    }

    public function test_homepage_makes_no_booking_payment_or_rating_claim(): void
    {
        $html = $this->home();

        foreach (['booking confirmed', 'payment successful', 'Flight Booked', 'Trusted by Thousands', 'Best Price Guarantee', '24/7 Support', '★', 'guaranteed admission'] as $claim) {
            $this->assertStringNotContainsString($claim, $html, 'the homepage makes an unsupported claim: '.$claim);
        }
    }

    public function test_homepage_chrome_carries_the_brief_navigation_and_footer(): void
    {
        $html = $this->home();

        foreach (['Student Visa', 'Visa &amp; Travel', 'About'] as $menu) {
            $this->assertStringContainsString('<summary>'.$menu.'</summary>', $html);
        }
        $this->assertStringContainsString('How it works', $html);
        $this->assertStringContainsString('Free Consultation', $html);
        $this->assertStringContainsString('data-sg-burger', $html);
        foreach (['Study', 'Services', 'Company', 'More from us'] as $column) {
            $this->assertStringContainsString('<h3>'.$column.'</h3>', $html);
        }
        $this->assertStringContainsString('href="'.route('privacy').'"', $html);
        $this->assertStringContainsString('css/egh-v2.css?v=', $html);
    }
}
