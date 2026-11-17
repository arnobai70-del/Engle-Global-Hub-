<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_site_photo_is_webp_under_300kb_and_credited(): void
    {
        $credits = (string) file_get_contents(base_path('docs/IMAGE_CREDITS.md'));
        $files = [...glob(public_path('images/malaysia/*')), ...glob(public_path('images/singapore/*'))];

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $name = 'public/images/'.basename(dirname($file)).'/'.basename($file);
            $this->assertStringEndsWith('.webp', $file);
            $this->assertSame('image/webp', getimagesize($file)['mime'] ?? null, $name);
            $this->assertLessThan(300 * 1000, filesize($file), $name.' is over 300 KB');
            $this->assertStringContainsString('`'.$name.'`', $credits, $name.' has no credit');
        }
        $this->assertStringContainsString('Unsplash License', $credits);
    }

    public function test_homepage_hero_uses_the_kuala_lumpur_photo_with_a_navy_overlay(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('v2-hero-photo', $html);
        $this->assertStringContainsString(asset('images/malaysia/petronas-towers-night.webp'), $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.asset('images/malaysia/petronas-towers-night.webp').'">', $html);
        $this->assertMatchesRegularExpression('/\.v2-hero\.v2-hero-photo \{\s*background:\s*linear-gradient\(90deg, rgba\(10, 31, 68, \.94\)/', (string) file_get_contents(public_path('css/egh-v2.css')));
    }

    public function test_public_pages_use_only_local_credited_photos(): void
    {
        foreach (['home', 'consultancy.malaysia', 'consultancy.singapore', 'why-us', 'about', 'consultancy.guides.index', 'consultancy.malaysia.guides.index'] as $page) {
            $this->assertStringNotContainsString('images.unsplash.com', (string) $this->get(route($page))->assertOk()->getContent(), $page);
        }
        $this->assertStringNotContainsString('images.unsplash.com', (string) file_get_contents(public_path('css/egh-sg.css')));
    }
}
