<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EmptyPagesHiddenTest extends TestCase
{
    use RefreshDatabase;

    private const EMPTY_PAGES = [
        'success-stories.index', 'offers.index', 'forums.index', 'reviews.index', 'rewards.index',
        'restaurants.index', 'inspiration.index', 'map', 'explore-bangladesh.index', 'consultancy.guides.index',
        'consultancy.institutions.index', 'mobile-app',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_empty_pages_are_not_linked_from_the_header_footer_or_sitemap_but_still_reachable_or_redirected(): void
    {
        $home = (string) $this->get(route('home'))->assertOk()->getContent();
        $sitemap = (string) $this->get(route('sitemap'))->assertOk()->getContent();

        foreach (self::EMPTY_PAGES as $page) {
            $this->assertStringNotContainsString('href="'.route($page).'"', $home, $page.' must not be linked while empty');
            $this->assertStringNotContainsString('<loc>'.route($page).'</loc>', $sitemap, $page.' must not be in the sitemap while empty');
            // These four send visitors to the homepage until they have content; the rest still open.
            in_array($page, ['success-stories.index', 'reviews.index', 'map', 'mobile-app'], true)
                ? $this->get(route($page))->assertRedirect(route('home'))
                : $this->get(route($page))->assertOk();
        }

        foreach (['consultancy.singapore', 'why-us', 'privacy', 'support', 'b2b.info'] as $page) {
            $this->assertStringContainsString('href="'.route($page), $home);
        }
        $this->assertStringContainsString('<loc>'.route('services.show', 'tourist-visa').'</loc>', $sitemap);
        $this->assertStringContainsString('<loc>'.route('privacy').'</loc>', $sitemap);
    }

    public function test_a_page_comes_back_automatically_once_content_is_published(): void
    {
        $guide = $this->entry(ContentType::Guide, 'students-pass', ContentEntry::STATUS_DRAFT);
        $this->get(route('home'))->assertDontSee('href="'.route('consultancy.guides.index').'"', false);

        $guide->forceFill(['status' => ContentEntry::STATUS_PUBLISHED])->save();
        $this->get(route('home'))->assertSee('href="'.route('consultancy.guides.index').'"', false);
        $this->get(route('sitemap'))->assertSee('<loc>'.route('consultancy.guides.index').'</loc>', false);
    }

    public function test_managed_menus_follow_the_same_rule(): void
    {
        $this->seed(MenuSeeder::class);
        $this->get(route('home'))->assertOk()
            ->assertDontSee('href="'.route('success-stories.index').'"', false)
            ->assertDontSee('href="'.route('forums.index').'"', false)
            ->assertSee('How it works')
            ->assertSee('href="'.route('services.show', 'export-import').'"', false);

        $this->entry(ContentType::SuccessStory, 'first-story', ContentEntry::STATUS_PUBLISHED);
        $this->get(route('home'))->assertSee('href="'.route('success-stories.index').'"', false);
    }

    private function entry(ContentType $type, string $slug, string $status): ContentEntry
    {
        $entry = new ContentEntry(['slug' => $slug, 'title' => 'Title '.$slug, 'summary' => 'Summary', 'body' => 'Body']);
        $entry->type = $type;
        $entry->status = $status;
        $entry->save();

        return $entry;
    }
}
