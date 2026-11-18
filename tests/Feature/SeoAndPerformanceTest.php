<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SeoAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_preloads_a_phone_sized_and_a_desktop_hero_image(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('href="'.asset('images/malaysia/petronas-towers-night-mobile.webp').'" type="image/webp" media="(max-width: 760px)" fetchpriority="high"', $html);
        $this->assertStringContainsString('href="'.asset('images/malaysia/petronas-towers-night.webp').'" type="image/webp" media="(min-width: 761px)" fetchpriority="high"', $html);
        $this->assertLessThan(100 * 1000, filesize(public_path('images/malaysia/petronas-towers-night-mobile.webp')));
    }

    public function test_homepage_has_organization_local_business_and_faq_schema(): void
    {
        $faq = new ContentEntry(['slug' => 'how-long', 'title' => 'How long does the Student Pass take?', 'summary' => 'S', 'body' => 'About two weeks for a complete application.']);
        $faq->type = ContentType::Faq;
        $faq->status = ContentEntry::STATUS_PUBLISHED;
        $faq->save();

        $schemas = $this->jsonLd((string) $this->get(route('home'))->assertOk()->getContent());
        $types = collect($schemas)->flatMap(fn (array $schema): array => $schema['@graph'] ?? [$schema])->pluck('@type')->flatten()->all();

        $this->assertContains('Organization', $types);
        $this->assertContains('LocalBusiness', $types);
        $this->assertContains('FAQPage', $types);
        $this->assertStringContainsString('How long does the Student Pass take?', json_encode($schemas, JSON_UNESCAPED_UNICODE));
    }

    public function test_every_public_page_has_an_og_image_and_english_and_bengali_hreflang(): void
    {
        foreach (['home', 'consultancy.malaysia', 'about', 'why-us', 'support', 'privacy'] as $page) {
            $html = (string) $this->get(route($page))->assertOk()->getContent();
            $base = route($page);

            $this->assertMatchesRegularExpression('/<meta property="og:image" content="https?:\/\/[^"]+">/', $html, $page.' has no og:image');
            $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="'.$base.'">', $html, $page);
            $this->assertStringContainsString('<link rel="alternate" hreflang="bn" href="'.$base.'?lang=bn">', $html, $page);
        }
    }

    public function test_marketing_pages_skip_the_heavy_stylesheets_and_other_pages_keep_them(): void
    {
        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('css/egh-site-base.css', $home);
        $this->assertStringNotContainsString('css/egh-ota.css', $home);
        $this->assertStringNotContainsString('/build/assets/app-', $home);

        $terms = (string) $this->get(route('terms'))->assertOk()->getContent();
        $this->assertStringContainsString('css/egh-ota.css', $terms);
        $this->assertStringNotContainsString('css/egh-site-base.css', $terms);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
    }
}
