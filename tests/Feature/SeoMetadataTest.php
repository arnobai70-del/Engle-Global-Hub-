<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Setting;
use App\Services\SettingService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SeoSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolePermissionSeeder::class, SeoSettingsSeeder::class]);
    }

    public function test_blank_seo_settings_keep_page_defaults(): void
    {
        $this->get(route('consultancy.singapore'))->assertOk()
            ->assertSee('<title>Study in Singapore from Bangladesh | Eagle Global Hub LTD</title>', false);
        $this->assertSame(18, Setting::query()->where('group', 'seo')->count());
    }

    public function test_admin_seo_override_replaces_title_description_and_social_tags_safely(): void
    {
        app(SettingService::class)->set('seo', 'consultancy.singapore.title', 'Singapore Study Consultancy for Bangladeshi Students', 'string', true);
        app(SettingService::class)->set('seo', 'consultancy.singapore.description', '<b>Free</b>   consultation for Singapore admission.', 'string', true);

        $this->get(route('consultancy.singapore'))->assertOk()
            ->assertSee('<title>Singapore Study Consultancy for Bangladeshi Students | Eagle Global Hub LTD</title>', false)
            ->assertSee('<meta name="description" content="Free consultation for Singapore admission.">', false)
            ->assertSee('<meta property="og:title" content="Singapore Study Consultancy for Bangladeshi Students">', false);

        $this->seed(SeoSettingsSeeder::class);
        $this->assertSame('Singapore Study Consultancy for Bangladeshi Students', app(SettingService::class)->get('seo', 'consultancy.singapore.title'));
    }

    public function test_sitemap_lists_public_pages_and_current_offers_only(): void
    {
        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('reviews.index'), false)
            ->assertDontSee(route('offers.index'), false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/my-applications', false);

        Offer::query()->create(['title' => 'Deal', 'slug' => 'deal', 'summary' => 'x', 'is_published' => true]);
        $this->get(route('sitemap'))->assertSee(route('offers.index'), false);
    }

    public function test_robots_txt_points_to_absolute_sitemap_and_blocks_private_areas(): void
    {
        $robots = (string) file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: https://eagleglobalhub.com/sitemap.xml', $robots);
        foreach (['/admin', '/my-applications', '/agent', '/notifications'] as $path) {
            $this->assertStringContainsString('Disallow: '.$path, $robots);
        }
    }
}
