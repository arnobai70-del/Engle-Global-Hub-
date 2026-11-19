<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\StudyDestination;
use App\Models\StudyDestinationItem;
use App\Services\Navigation\MenuService;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\LegacyMenus\MenuSeederV1;
use Tests\Fixtures\LegacyMenus\MenuSeederV2;
use Tests\Fixtures\LegacyMenus\MenuSeederV3;
use Tests\TestCase;

/**
 * Runs against a database shaped like the live site: the menus of the first three
 * releases seeded in order, then today's ProductionSeeder twice.
 */
final class MenuTidyTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_shaped_menus_are_tidied_without_deleting_anything(): void
    {
        $this->seedLiveShapedMenus();
        $before = MenuItem::query()->count();

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertSame($before + 6, MenuItem::query()->count(), 'only the six missing Explore sub-items are added; nothing is deleted');
        $menu = app(MenuService::class);

        $visa = $this->children($menu->tree('header'), 'Visa Services');
        $travel = $this->children($menu->tree('header'), 'Travel Services');
        $this->assertSame(['Visa information', 'Tourist Visa', 'Business Visa', 'Work Visa'], $visa);
        $this->assertEmpty(array_intersect(['Tourist Visa', 'Business Visa', 'Work Visa'], $travel), 'visas only under Visa Services');
        $this->assertContains('Foreigner Support', $travel);
        $this->assertContains('Air Ticket', $travel);
        $this->assertSame(['About Us', 'Why Us'], $this->children($menu->tree('header'), 'About'), 'Contact is a top-level link already');

        $footer = $menu->tree('footer');
        $this->assertNotContains('Quick Links', array_column($footer, 'label'));
        $study = $this->children($footer, 'Study Abroad');
        $this->assertSame('Study in Malaysia', $study[0]);
        $this->assertNotContains('Student Consultancy', $study, 'now the same link as Study in Malaysia, so shown once');
        $this->assertSame('route:consultancy.malaysia', MenuItem::query()->where('key', 'footer-consultancy')->value('target'));
        $this->assertNotContains('Hotels & tours', $this->children($footer, 'Our Services'));
        $this->assertContains('Export & Import', $this->children($footer, 'More from us'), 'business links stay in the footer');

        $this->assertSame(route('consultancy.malaysia').'#apply', $menu->tree('header_cta')[0]['url']);
        $this->assertSame('Study in Malaysia', $menu->tree('home_tiles')[0]['label']);

        foreach (['header', 'footer'] as $location) {
            $links = collect($menu->tree($location))->flatMap(fn (array $top): array => $top['children'] !== [] ? array_column($top['children'], 'url') : [$top['url']])->filter()->values();
            $this->assertSame($links->unique()->values()->all(), $links->all(), $location.' renders a link twice');
        }

        $html = (string) $this->get(route('home'))->assertOk()->getContent();
        $header = (string) strstr($html, '</header>', true);
        $this->assertSame(1, substr_count((string) strstr($header, '<details class="sg-dd sg-dd-mega"'), '>Free Consultation</a>'), 'one Free Consultation under Student Visa');
    }

    public function test_footer_columns_and_the_explore_menu_are_tidied_on_live_shaped_menus(): void
    {
        $this->seedLiveShapedMenus();

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);
        $menu = app(MenuService::class);

        $footer = $menu->tree('footer');
        $this->assertContains('Explore Bangladesh', $this->children($footer, 'Our Services'));
        $this->assertSame(['Export & Import', 'Civil & Construction Work', 'Become a partner (B2B)'], $this->children($footer, 'More from us'));
        $this->assertFalse((bool) MenuItem::query()->where('key', 'footer-search')->value('is_active'), 'hidden, not deleted');
        $this->assertStringContainsString('href="'.route('search').'"', (string) strstr((string) $this->get(route('home'))->getContent(), '<footer'), 'Search moves to the bottom bar');

        $explore = collect($menu->tree('header'))->firstWhere('label', 'Explore Bangladesh');
        $this->assertSame('All destinations', $explore['children'][0]['label']);
        $this->assertSame(route('explore-bangladesh.index'), $explore['children'][0]['url']);
        $this->assertSame(1, MenuItem::query()->where('label_en', 'All destinations')->count(), 'created once, not on every run');
        $categories = array_filter(array_column($explore['children'], 'url'), fn (string $url): bool => str_contains($url, '?category='));
        $this->assertNotEmpty($categories, 'category links are not treated as repeats of the Explore page');
    }

    public function test_the_seeded_home_link_is_hidden_and_study_destinations_are_seeded_once_as_drafts(): void
    {
        $this->seedLiveShapedMenus();
        $before = MenuItem::query()->count();

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $home = MenuItem::query()->where('location', 'header')->where('key', 'home')->firstOrFail();
        $this->assertFalse((bool) $home->is_active, 'hidden, not deleted');
        $this->assertNotContains('Home', array_column(app(MenuService::class)->tree('header'), 'label'));
        $this->assertStringContainsString('>Home</a>', (string) $this->get(route('consultancy.malaysia'))->getContent(), 'breadcrumbs keep Home');
        $this->assertSame($before + 6, MenuItem::query()->count());

        $this->assertSame(5, StudyDestination::query()->count());
        $this->assertSame(0, StudyDestination::query()->published()->count(), 'the seeder publishes nothing');
        $this->assertSame(7, StudyDestinationItem::query()->count());
        $this->assertSame(StudyDestination::query()->count(), StudyDestination::query()->distinct()->count('country_id'));
    }

    public function test_an_edited_home_link_is_left_alone(): void
    {
        $this->seedLiveShapedMenus();
        MenuItem::query()->where('location', 'header')->where('key', 'home')->update(['label_en' => 'Start']);

        $this->seed(ProductionSeeder::class);

        $this->assertTrue((bool) MenuItem::query()->where('location', 'header')->where('key', 'home')->value('is_active'));
    }

    public function test_footer_items_an_admin_edited_stay_where_they_are(): void
    {
        $this->seedLiveShapedMenus();
        MenuItem::query()->where('key', 'footer-search')->update(['label_en' => 'Search the site']);
        MenuItem::query()->where('key', 'footer-explore')->update(['label_en' => 'Visit Bangladesh']);
        $explore = MenuItem::query()->where('key', 'footer-explore')->first();

        $this->seed(ProductionSeeder::class);

        $this->assertTrue((bool) MenuItem::query()->where('key', 'footer-search')->value('is_active'));
        $this->assertSame($explore->parent_id, MenuItem::query()->where('key', 'footer-explore')->value('parent_id'));
    }

    public function test_a_child_repeating_its_parent_link_is_kept_only_as_the_first_item(): void
    {
        $item = static fn (array $attributes): MenuItem => MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true], $attributes));
        $explore = $item(['label_en' => 'Explore', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 1]);
        $item(['parent_id' => $explore->id, 'label_en' => 'All destinations', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 1]);
        $item(['parent_id' => $explore->id, 'label_en' => 'Beaches', 'target' => 'route:explore-bangladesh.index|category=beach', 'sort_order' => 2]);
        $item(['parent_id' => $explore->id, 'label_en' => 'Explore again', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 3]);

        $tree = app(MenuService::class)->tree('header');

        $this->assertSame(['All destinations', 'Beaches'], array_column($tree[0]['children'], 'label'));
    }

    public function test_items_an_admin_edited_are_left_alone(): void
    {
        $this->seedLiveShapedMenus();
        MenuItem::query()->where('key', 'travel-tourist-visa')->update(['label_en' => 'Visas for tourists']);
        MenuItem::query()->where('key', 'cta-apply')->update(['label_en' => 'Apply today']);
        MenuItem::query()->where('key', 'footer-study')->update(['label_en' => 'Our study options']);
        MenuItem::query()->where('key', 'about-export')->update(['is_active' => false]);

        $this->seed(ProductionSeeder::class);

        $this->assertTrue((bool) MenuItem::query()->where('key', 'travel-tourist-visa')->value('is_active'));
        $this->assertSame('route:consultancy.singapore', MenuItem::query()->where('key', 'cta-apply')->value('target'));
        $this->assertSame('Our study options', MenuItem::query()->where('key', 'footer-study')->value('label_en'));
        $this->assertFalse((bool) MenuItem::query()->where('key', 'about-export')->value('is_active'), 'hidden stays hidden');
    }

    public function test_a_fresh_install_keeps_its_menus(): void
    {
        $this->seed(ProductionSeeder::class);
        $menu = app(MenuService::class);

        $this->assertContains('Tourist Visa', $this->children($menu->tree('header'), 'Visa & Travel'), 'nothing to de-duplicate without a separate Visa Services menu');
        $this->assertSame('Study Abroad', $menu->tree('footer')[0]['label']);
        $this->assertSame(route('consultancy.malaysia').'#apply', $menu->tree('header_cta')[0]['url']);
    }

    public function test_a_top_level_link_is_not_repeated_inside_a_dropdown(): void
    {
        $item = static fn (array $attributes): MenuItem => MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true], $attributes));
        $about = $item(['label_en' => 'About', 'sort_order' => 1]);
        $item(['parent_id' => $about->id, 'label_en' => 'About Us', 'target' => 'route:about', 'sort_order' => 1]);
        $item(['parent_id' => $about->id, 'label_en' => 'Contact Us', 'target' => 'route:support', 'sort_order' => 2]);
        $item(['label_en' => 'Contact', 'target' => 'route:support', 'sort_order' => 2]);
        $more = $item(['label_en' => 'More', 'sort_order' => 3]);
        $item(['parent_id' => $more->id, 'label_en' => 'About again', 'target' => 'route:about', 'sort_order' => 1]);

        $tree = app(MenuService::class)->tree('header');

        $this->assertSame(['About', 'Contact'], array_column($tree, 'label'), 'a dropdown left without links is dropped');
        $this->assertSame(['About Us'], array_column($tree[0]['children'], 'label'));
    }

    private function seedLiveShapedMenus(): void
    {
        $this->seed(RolePermissionSeeder::class);
        foreach ([MenuSeederV1::class, MenuSeederV2::class, MenuSeederV3::class] as $seeder) {
            $this->seed($seeder);
        }
        (require collect(glob(database_path('migrations/*merge_study_menus_into_student_visa.php')))->sole())->up();
    }

    /**
     * @param  list<array<string, mixed>>|null  $tree
     * @return list<string>
     */
    private function children(?array $tree, string $label): array
    {
        $top = collect($tree)->firstWhere('label', $label);
        $this->assertNotNull($top, $label.' is missing');

        return array_column($top['children'], 'label');
    }
}
