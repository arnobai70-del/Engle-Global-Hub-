<?php

namespace Tests\Feature;

use App\Services\Travel\ServiceCatalog;
use App\Services\Travel\ServicePageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServicePageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_foreigner_support_shows_travel_documents_and_travel_related_services(): void
    {
        $html = (string) $this->get(route('services.show', 'foreigner-support'))->assertOk()->getContent();

        foreach (['Passport copy', 'Travel dates', 'Number of travellers', 'The cities or places you want to visit', 'Your hotel and transport needs'] as $document) {
            $this->assertStringContainsString($document, $html);
        }
        $this->assertStringNotContainsString('drawings, specifications or permits', $html);

        $related = $this->relatedTitles($html);
        $this->assertSame(['Explore Bangladesh', 'Tours', 'Hotels', 'Tourist Visa'], $related);
    }

    public function test_footer_band_fits_the_service(): void
    {
        $foreigner = (string) $this->get(route('services.show', 'foreigner-support'))->assertOk()->getContent();
        $this->assertStringContainsString('data-footer-cta="travel"', $foreigner);
        $this->assertStringNotContainsString('Ready to study in Malaysia?', $foreigner);

        $this->assertStringContainsString('Ready to study in Malaysia?', (string) $this->get(route('services.show', 'student-visa'))->getContent());
    }

    public function test_every_service_page_fits_its_own_service(): void
    {
        $pages = app(ServicePageData::class);
        $business = ['export-import', 'civil-construction'];

        foreach (array_keys(app(ServiceCatalog::class)->all()) as $slug) {
            $page = $pages->for($slug);
            $this->assertNotNull($page, $slug);
            $html = (string) $this->get(route('services.show', $slug))->assertOk()->getContent();

            if ($slug !== 'civil-construction') {
                $this->assertNotContains('Any drawings, specifications or permits you already have', $page['documents'], $slug.' shows construction documents');
            }
            if (! in_array($slug, $business, true)) {
                $this->assertEmpty(array_intersect($this->relatedTitles($html), ['Export & Import', 'Civil & Construction Work']), $slug.' links to business services');
            }
            $this->assertNotContains($page['title'], $this->relatedTitles($html), $slug.' lists itself');
            $this->assertNotEmpty($page['steps'], $slug);
            $this->assertNotEmpty($page['documents'], $slug);
        }
    }

    /**
     * @return list<string>
     */
    private function relatedTitles(string $html): array
    {
        $section = (string) strstr((string) strstr($html, 'id="related-title"'), '</section>', true);
        preg_match_all('#<a [^>]*>(?:<[^>]+>)*\s*([^<]+?)\s*<#', $section, $matches);

        return array_values(array_filter(array_map(fn (string $text): string => html_entity_decode(trim($text), ENT_QUOTES), $matches[1]), fn (string $text): bool => $text !== '' && $text !== '→'));
    }
}
