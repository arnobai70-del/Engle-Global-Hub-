<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Institution;
use App\Models\MediaAsset;
use App\Services\SettingService;
use App\Services\SiteFacts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class SiteTrustTest extends TestCase
{
    use RefreshDatabase;

    public function test_trust_blocks_are_hidden_while_the_owner_has_entered_nothing(): void
    {
        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $about = (string) $this->get(route('about'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Institutions we work with', $home);
        $this->assertStringNotContainsString('class="v2-numbers"', $about);
        $this->assertStringNotContainsString('Registered and accountable', $about);
        $this->assertStringNotContainsString('Students who made it', (string) $this->get(route('why-us'))->getContent());
        $this->assertStringNotContainsString('sg-footer-social', $home);
    }

    public function test_testimonials_show_name_programme_university_intake_and_photo(): void
    {
        $photo = MediaAsset::query()->create(['collection' => 'general', 'path' => 'media/nadia.webp', 'original_name' => 'nadia.webp', 'mime_type' => 'image/webp', 'size_bytes' => 1000, 'alt_text' => 'Nadia smiling', 'is_published' => true]);
        foreach (range(1, 5) as $number) {
            $story = new ContentEntry([
                'slug' => 'story-'.$number, 'title' => 'Student '.$number, 'summary' => 'They explained every EMGS step '.$number.'.',
                'category' => 'Diploma in Business', 'location' => 'Test University', 'intake' => 'September 2025',
                'cover_media_id' => $number === 1 ? $photo->id : null, 'sort_order' => $number,
            ]);
            $story->type = ContentType::SuccessStory;
            $story->status = ContentEntry::STATUS_PUBLISHED;
            $story->save();
        }

        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertSame(3, substr_count($home, 'class="v2-story"'), 'three on the homepage');
        $this->assertStringContainsString('Diploma in Business · Test University · September 2025', $home);
        $this->assertStringContainsString('alt="Nadia smiling"', $home);

        $this->assertSame(5, substr_count((string) $this->get(route('why-us'))->assertOk()->getContent(), 'class="v2-story"'), 'more on Why Us');
    }

    public function test_numbers_credentials_and_social_links_come_only_from_admin_settings(): void
    {
        $this->facts(['students_guided' => '250', 'founded_year' => (string) (now()->year - 6), 'visa_files_processed' => '900', 'company_registration_no' => 'C-123456', 'memberships' => "ATAB member 1234\nTOAB member 77", 'google_maps_url' => 'https://maps.app.goo.gl/example']);
        app(SettingService::class)->set('social', 'tiktok_url', 'https://www.tiktok.com/@example', 'string', true);

        $about = (string) $this->get(route('about'))->assertOk()->getContent();
        $this->assertStringContainsString('250+', $about);
        $this->assertStringContainsString('years in business', $about);
        $this->assertStringContainsString('900+', $about);
        $this->assertStringContainsString('C-123456', $about);
        $this->assertStringContainsString('<li>TOAB member 77</li>', $about);
        $this->assertStringContainsString('href="https://maps.app.goo.gl/example"', $about);

        $footer = (string) strstr($about, '<footer');
        $this->assertStringContainsString('Registration C-123456', $footer);
        $this->assertStringContainsString('aria-label="TikTok"', $footer);
        $this->assertStringNotContainsString('aria-label="Facebook"', $footer, 'empty networks stay hidden');
    }

    public function test_partner_logo_strip_shows_only_public_institutions_with_a_logo(): void
    {
        $malaysia = Country::query()->create(['name' => 'Malaysia', 'iso2' => 'MY', 'iso3' => 'MYS', 'is_active' => true]);
        $logo = MediaAsset::query()->create(['collection' => 'partner_logos', 'path' => 'media/kl-logo.webp', 'original_name' => 'kl-logo.webp', 'mime_type' => 'image/webp', 'size_bytes' => 1000, 'alt_text' => 'KL University logo', 'width' => 200, 'height' => 80, 'is_published' => true]);
        $listed = Institution::query()->create(['name' => 'KL University', 'status' => Institution::STATUS_ACTIVE, 'country_id' => $malaysia->id, 'logo_media_id' => $logo->id]);
        $listed->forceFill(['is_public' => true, 'publicly_verified_at' => now()])->save();
        Institution::query()->create(['name' => 'Hidden College', 'status' => Institution::STATUS_ACTIVE, 'country_id' => $malaysia->id, 'logo_media_id' => $logo->id]);

        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Institutions we work with', $home);
        $this->assertSame(1, substr_count($home, 'alt="KL University logo"'));
        $this->assertStringContainsString('width="200" height="80" loading="lazy"', $home);
    }

    public function test_scroll_reveal_never_leaves_content_hidden(): void
    {
        $script = (string) file_get_contents(public_path('js/egh-sg-nav.js'));

        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)').matches) return;", $script, 'no animation with reduced motion');
        $this->assertStringContainsString("rootMargin: '0px 0px 25% 0px', threshold: 0", $script, 'blocks start fading in before they are on screen');
        $this->assertStringContainsString("window.addEventListener('beforeprint', revealAll);", $script);
        $this->assertStringContainsString('setTimeout(revealAll, 4000)', $script, 'safety net after load');
        $this->assertStringNotContainsString('egh-reveal', (string) $this->get(route('home'))->getContent(), 'the server never sends hidden content');
    }

    public function test_travel_forms_ask_for_international_numbers_and_student_forms_keep_the_bangladesh_hint(): void
    {
        $this->assertStringContainsString('placeholder="+44 7700 900123"', (string) $this->get(route('services.show', 'foreigner-support'))->getContent());
        $this->assertStringContainsString('Include the country code', (string) $this->get(route('services.show', 'tourist-visa'))->getContent());
        $this->assertStringContainsString('placeholder="01XXXXXXXXX"', (string) $this->get(route('home'))->getContent(), 'student consultation form');
        $this->assertStringContainsString('placeholder="01XXXXXXXXX"', (string) $this->get(route('services.show', 'student-visa'))->getContent());
    }

    public function test_forms_keep_input_on_errors_and_confirm_success_in_bengali(): void
    {
        Notification::fake();

        $this->from(route('services.show', ['slug' => 'foreigner-support', 'lang' => 'bn']))
            ->post(route('leads.quote', 'foreigner-support'), ['full_name' => 'Emma Clarke', 'phone' => 'not a number', 'consent' => '1'])
            ->assertSessionHasErrors('phone');
        $this->get(route('services.show', ['slug' => 'foreigner-support', 'lang' => 'bn']))->assertSee('value="Emma Clarke"', false);

        $this->from(route('services.show', 'foreigner-support'))
            ->post(route('leads.quote', 'foreigner-support'), ['full_name' => 'Emma Clarke', 'phone' => '+44 7700 900123', 'consent' => '1'])
            ->assertSessionHas('lead_reference');
        $this->get(route('services.show', ['slug' => 'foreigner-support', 'lang' => 'bn']))->assertOk()->assertSee(__('Thank you, we received your request.', [], 'bn'));
    }

    /**
     * @param  array<string, string>  $values
     */
    private function facts(array $values): void
    {
        foreach ($values as $key => $value) {
            app(SettingService::class)->set(SiteFacts::GROUP, $key, $value, 'string', true);
        }
    }
}
