<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use Database\Seeders\ExploreBangladeshMenuSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ExploreMenuDuplicateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The forms an older, admin-made Explore Bangladesh item can have.
     *
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function oldItems(): array
    {
        return [
            'route target, no key' => [['label_en' => 'Explore Bangladesh', 'target' => 'route:explore-bangladesh.index']],
            'https link to the page' => [['label_en' => 'Explore BD', 'target' => 'url:https://eagleglobalhub.com/explore-bangladesh/']],
            'heading with the label' => [['label_en' => 'Explore  bangladesh', 'target' => null]],
            'Bengali label only' => [['label_en' => 'Tourism', 'label_bn' => 'বাংলাদেশ ঘুরে দেখুন', 'target' => null]],
            'other key, with fragment' => [['key' => 'explore', 'label_en' => 'Visit BD', 'target' => 'route:explore-bangladesh.index', 'fragment' => 'top']],
        ];
    }

    /**
     * @param  array<string, mixed>  $old
     */
    #[DataProvider('oldItems')]
    public function test_an_existing_explore_item_is_reused_and_seeding_twice_leaves_one_item_with_seven_children(array $old): void
    {
        $this->seed(MenuSeeder::class);
        $existing = $this->top($old + ['sort_order' => 35]);

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $visible = $this->visibleExploreItems();
        $this->assertCount(1, $visible);
        $this->assertTrue($visible->first()->is($existing), 'the old item is reused, not replaced');
        $this->assertSame(7, $existing->children()->where('is_active', true)->count());
        $this->assertSame($old['label_en'], $existing->fresh()->label_en, 'never renamed');
        $this->assertSame(35, $existing->fresh()->sort_order, 'never re-ordered');
        $header = (string) strstr((string) $this->get(route('home'))->getContent(), '</header>', true);
        $this->assertSame(1, substr_count($header, '>'.e($old['label_en']).'<'), 'shown once in the header');
    }

    public function test_a_database_with_the_duplicate_is_cleaned_to_one_visible_item_without_deleting(): void
    {
        $this->seed(MenuSeeder::class);
        // Live state after the first release: an older admin item plus the item the seeder added.
        $old = $this->top(['label_en' => 'Explore Bangladesh', 'target' => 'url:https://eagleglobalhub.com/explore-bangladesh', 'sort_order' => 70]);
        $oldChild = MenuItem::query()->create(['location' => 'header', 'parent_id' => $old->id, 'label_en' => 'Sylhet', 'target' => 'route:explore-bangladesh.index', 'fragment' => 'sylhet', 'is_active' => true, 'sort_order' => 1]);
        $seeded = $this->top(['key' => ExploreBangladeshMenuSeeder::KEY, 'label_en' => 'Explore Bangladesh', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 30]);
        foreach (ExploreBangladeshMenuSeeder::children() as $position => $child) {
            MenuItem::query()->create(['location' => 'header', 'parent_id' => $seeded->id, 'key' => 'explore-bangladesh-'.$child['key'], 'label_en' => $child['label_en'], 'target' => $child['target'], 'fragment' => $child['fragment'] ?? null, 'is_active' => true, 'sort_order' => $position]);
        }
        $before = MenuItem::query()->count();

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        // Both have sub-items, so the older one is kept and receives the others.
        $this->assertSame([$old->id], $this->visibleExploreItems()->pluck('id')->all());
        $this->assertFalse($seeded->fresh()->is_active, 'the duplicate is hidden');
        $this->assertNotNull($seeded->fresh(), 'and not deleted');
        $this->assertSame($before, MenuItem::query()->count(), 'nothing deleted, nothing added');
        $this->assertSame($old->id, $oldChild->fresh()->parent_id);
        $this->assertSame(8, $old->children()->where('is_active', true)->count(), 'its own sub-item plus the seven moved over');
        $this->assertSame(ExploreBangladeshMenuSeeder::KEY, $old->fresh()->key, 'the kept item takes over the stable key');

        $header = (string) strstr((string) $this->get(route('home'))->getContent(), '</header>', true);
        $this->assertSame(1, substr_count($header, '<summary>Explore Bangladesh</summary>'));
    }

    public function test_the_item_with_children_is_kept_when_the_older_one_has_none(): void
    {
        $this->seed(MenuSeeder::class);
        $old = $this->top(['label_en' => 'Explore Bangladesh', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 70]);
        $seeded = $this->top(['key' => ExploreBangladeshMenuSeeder::KEY, 'label_en' => 'Explore Bangladesh', 'target' => 'route:explore-bangladesh.index', 'sort_order' => 30]);
        MenuItem::query()->create(['location' => 'header', 'parent_id' => $seeded->id, 'label_en' => 'All destinations', 'target' => 'route:explore-bangladesh.index', 'is_active' => true, 'sort_order' => 1]);

        $this->seed(ProductionSeeder::class);

        $this->assertSame([$seeded->id], $this->visibleExploreItems()->pluck('id')->all());
        $this->assertFalse($old->fresh()->is_active);
        $this->assertSame(7, $seeded->children()->where('is_active', true)->count(), 'its child plus the six missing ones');
    }

    public function test_an_item_the_admin_hid_stays_hidden_and_no_new_one_appears(): void
    {
        $this->seed(MenuSeeder::class);
        $hidden = $this->top(['label_en' => 'Explore Bangladesh', 'target' => 'route:explore-bangladesh.index', 'is_active' => false, 'sort_order' => 35]);

        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertFalse($hidden->fresh()->is_active);
        $this->assertCount(0, $this->visibleExploreItems());
        $this->assertSame(1, ExploreBangladeshMenuSeeder::existingItems()->count());
        $this->assertStringNotContainsString('<summary>Explore Bangladesh</summary>', (string) $this->get(route('home'))->getContent());
    }

    public function test_header_never_shows_two_top_level_items_with_the_same_link(): void
    {
        $this->seed(MenuSeeder::class);
        $this->top(['label_en' => 'Our Visa Help', 'target' => 'route:visa.index', 'sort_order' => 1]);
        $this->top(['label_en' => 'Visa again', 'target' => 'route:visa.index', 'sort_order' => 2]);

        $html = (string) $this->get(route('home'))->getContent();

        $this->assertStringContainsString('Our Visa Help', $html);
        $this->assertStringNotContainsString('Visa again', $html);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function top(array $attributes): MenuItem
    {
        return MenuItem::query()->create(array_merge(['location' => 'header', 'visibility' => 'all', 'is_active' => true], $attributes));
    }

    /**
     * @return Collection<int, MenuItem>
     */
    private function visibleExploreItems(): Collection
    {
        return ExploreBangladeshMenuSeeder::existingItems()->filter(fn (MenuItem $item): bool => $item->is_active)->values();
    }
}
