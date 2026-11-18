<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Adds the "Explore Bangladesh" dropdown to the admin-managed header menu, right after
 * "Travel Services" (or "Visa & Travel"). Safe on the live site:
 * - it does nothing while the header still uses the built-in menu (no managed items);
 * - it does nothing when an Explore Bangladesh item already exists (by key or by link),
 *   so an item an admin edited, moved or hid is never touched or duplicated;
 * - sub-items are added only together with a new parent, so deleted ones never return.
 */
class ExploreBangladeshMenuSeeder extends Seeder
{
    public const KEY = 'explore-bangladesh';

    public function run(): void
    {
        $header = MenuItem::query()->where('location', 'header');
        if (! (clone $header)->exists()) {
            return;
        }
        if ((clone $header)->where(fn ($query) => $query->where('key', self::KEY)
            ->orWhere(fn ($top) => $top->whereNull('parent_id')->where('target', 'route:explore-bangladesh.index')))->exists()) {
            return;
        }

        $parent = MenuItem::query()->create([
            'location' => 'header',
            'key' => self::KEY,
            'label_en' => 'Explore Bangladesh',
            'label_bn' => 'বাংলাদেশ ঘুরে দেখুন',
            'target' => 'route:explore-bangladesh.index',
            'visibility' => 'all',
            'is_active' => true,
            'sort_order' => $this->sortOrderAfterTravel(),
        ]);

        foreach ($this->children() as $position => $child) {
            MenuItem::query()->create([
                'location' => 'header',
                'parent_id' => $parent->id,
                'key' => self::KEY.'-'.$child['key'],
                'label_en' => $child['label_en'],
                'label_bn' => $child['label_bn'],
                'target' => $child['target'],
                'fragment' => $child['fragment'] ?? null,
                'visibility' => 'all',
                'is_active' => true,
                'sort_order' => ($position + 1) * 10,
            ]);
        }
    }

    /**
     * Same order as "Travel Services" (or the travel dropdown): the higher id of the new
     * item places it right after that item without renumbering anything an admin set.
     */
    private function sortOrderAfterTravel(): int
    {
        $top = MenuItem::query()->where('location', 'header')->whereNull('parent_id');
        $anchor = (clone $top)->where('label_en', 'Travel Services')->first()
            ?? (clone $top)->where('key', 'travel')->first()
            ?? (clone $top)->where('label_en', 'Visa & Travel')->first();

        return $anchor !== null ? (int) $anchor->sort_order : (int) (clone $top)->max('sort_order') + 10;
    }

    /**
     * @return list<array{key: string, label_en: string, label_bn: string, target: string, fragment?: string}>
     */
    private function children(): array
    {
        $index = 'route:explore-bangladesh.index';

        return [
            ['key' => 'all', 'label_en' => 'All destinations', 'label_bn' => 'সব দর্শনীয় স্থান', 'target' => $index],
            ['key' => 'historical', 'label_en' => 'Historical & heritage', 'label_bn' => 'ঐতিহাসিক ও ঐতিহ্য', 'target' => $index.'|category=historical'],
            ['key' => 'beach', 'label_en' => 'Beaches & islands', 'label_bn' => 'সমুদ্রসৈকত ও দ্বীপ', 'target' => $index.'|category=beach'],
            ['key' => 'hills', 'label_en' => 'Hills & tea gardens', 'label_bn' => 'পাহাড় ও চা-বাগান', 'target' => $index.'|category=hills'],
            ['key' => 'nature', 'label_en' => 'Forests & wildlife', 'label_bn' => 'বন ও বন্যপ্রাণী', 'target' => $index.'|category=nature'],
            ['key' => 'essentials', 'label_en' => 'Travel essentials for foreigners', 'label_bn' => 'বিদেশিদের জন্য ভ্রমণ তথ্য', 'target' => $index, 'fragment' => 'essentials'],
            ['key' => 'plan-trip', 'label_en' => 'Plan my trip', 'label_bn' => 'ভ্রমণ পরিকল্পনা করুন', 'target' => $index, 'fragment' => 'plan-trip'],
        ];
    }
}
