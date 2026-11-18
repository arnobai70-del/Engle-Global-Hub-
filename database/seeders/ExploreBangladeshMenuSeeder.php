<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Services\SettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The "Explore Bangladesh" dropdown in the admin-managed header menu. Safe to run on the live
 * site again and again:
 *
 * 1. An existing top-level Explore Bangladesh item is found by key, by link (the route target,
 *    an https link to /explore-bangladesh, with or without a fragment) or by its English or
 *    Bengali label, active or hidden, and REUSED. Nothing an admin set is renamed, re-ordered
 *    or re-enabled; only empty fields are filled.
 * 2. If several such items exist (the duplicate created by the first release), the one with
 *    sub-items is kept (or the oldest); the others' sub-items move onto it and the others are
 *    hidden, never deleted.
 * 3. The seven sub-items are attached once per item; ones an admin deleted later never return.
 * 4. A new item is created only when none exists, right after "Travel Services".
 * Nothing happens while the header still uses the built-in menu (no managed items).
 */
class ExploreBangladeshMenuSeeder extends Seeder
{
    public const KEY = 'explore-bangladesh';

    /** Remembers which item already received the seven sub-items. */
    private const CHILDREN_FLAG = ['menus', 'explore_bangladesh_children_for'];

    private const LABELS = ['explore bangladesh', 'বাংলাদেশ ঘুরে দেখুন'];

    public function __construct(private SettingService $settings) {}

    public function run(): void
    {
        if (! MenuItem::query()->where('location', 'header')->exists()) {
            return;
        }

        DB::transaction(function (): void {
            $existing = self::existingItems();
            $item = $existing->isEmpty() ? $this->create() : $this->merge($existing);
            $this->fillEmptyFields($item);
            $this->attachChildrenOnce($item);
        });
    }

    /**
     * Every top-level header item that is an "Explore Bangladesh" item, oldest first.
     *
     * @return Collection<int, MenuItem>
     */
    public static function existingItems(): Collection
    {
        return MenuItem::query()->where('location', 'header')->whereNull('parent_id')->with('children')->orderBy('id')->get()
            ->filter(fn (MenuItem $item): bool => self::isExploreItem($item))->values();
    }

    public static function isExploreItem(MenuItem $item): bool
    {
        if ($item->key === self::KEY) {
            return true;
        }
        if (self::isExploreTarget($item->target)) {
            return true;
        }
        $label = static fn (?string $text): string => mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $text)));
        if (in_array($label($item->label_en), self::LABELS, true) || in_array($label($item->label_bn), self::LABELS, true)) {
            return true;
        }

        // A heading without a link whose sub-items all open Explore Bangladesh pages.
        return $item->target === null && $item->children->isNotEmpty()
            && $item->children->every(fn (MenuItem $child): bool => self::isExploreTarget($child->target));
    }

    /**
     * "route:explore-bangladesh.index" (any parameter) or an https link whose path is /explore-bangladesh.
     */
    public static function isExploreTarget(?string $target): bool
    {
        if ($target === null) {
            return false;
        }
        if (str_starts_with($target, 'route:')) {
            return explode('|', substr($target, 6), 2)[0] === 'explore-bangladesh.index';
        }
        if (str_starts_with($target, 'url:')) {
            return rtrim((string) parse_url(substr($target, 4), PHP_URL_PATH), '/') === '/explore-bangladesh';
        }

        return false;
    }

    private function create(): MenuItem
    {
        return MenuItem::query()->create([
            'location' => 'header',
            'key' => self::KEY,
            'label_en' => 'Explore Bangladesh',
            'label_bn' => 'বাংলাদেশ ঘুরে দেখুন',
            'target' => 'route:explore-bangladesh.index',
            'visibility' => 'all',
            'is_active' => true,
            'sort_order' => $this->sortOrderAfterTravel(),
        ]);
    }

    /**
     * Keeps one item: the oldest one that has sub-items, otherwise the oldest. The others'
     * sub-items move onto it (a sub-item repeating a link already there is hidden instead),
     * and the other items are hidden. Nothing is deleted.
     *
     * @param  Collection<int, MenuItem>  $items
     */
    private function merge(Collection $items): MenuItem
    {
        $keeper = $items->first(fn (MenuItem $item): bool => $item->children->isNotEmpty()) ?? $items->first();

        foreach ($items->reject(fn (MenuItem $item): bool => $item->is($keeper)) as $duplicate) {
            foreach ($duplicate->children as $child) {
                $repeats = $keeper->children()->where('target', $child->target)
                    ->when($child->fragment === null, fn ($query) => $query->whereNull('fragment'), fn ($query) => $query->where('fragment', $child->fragment))->exists();
                $child->forceFill(['parent_id' => $keeper->id, 'is_active' => $repeats ? false : $child->is_active])->save();
            }
            // Free the stable key for the item we keep; the hidden duplicate keeps everything else.
            $duplicate->forceFill(['is_active' => false, 'key' => $duplicate->key === self::KEY ? null : $duplicate->key])->save();
        }

        return $keeper->refresh();
    }

    private function fillEmptyFields(MenuItem $item): void
    {
        if ($item->key === null && ! MenuItem::query()->where('location', 'header')->where('key', self::KEY)->exists()) {
            $item->key = self::KEY;
        }
        if (blank($item->label_bn)) {
            $item->label_bn = 'বাংলাদেশ ঘুরে দেখুন';
        }
        // A heading keeps working as a dropdown; the link only helps when every sub-item is hidden.
        if ($item->target === null) {
            $item->target = 'route:explore-bangladesh.index';
        }
        if ($item->isDirty()) {
            $item->save();
        }
    }

    /**
     * Adds the seven sub-items that are missing (matched by link), once per item.
     */
    private function attachChildrenOnce(MenuItem $item): void
    {
        [$group, $key] = self::CHILDREN_FLAG;
        if ((string) rescue(fn () => $this->settings->get($group, $key), '', false) === (string) $item->id) {
            return;
        }

        $present = $item->children()->get(['target', 'fragment'])->map(fn (MenuItem $child): string => $child->target.'#'.$child->fragment)->all();
        $position = (int) $item->children()->max('sort_order');
        foreach (self::children() as $child) {
            if (in_array($child['target'].'#'.($child['fragment'] ?? ''), $present, true)) {
                continue;
            }
            $childKey = self::KEY.'-'.$child['key'];
            MenuItem::query()->create([
                'location' => 'header',
                'parent_id' => $item->id,
                'key' => MenuItem::query()->where('location', 'header')->where('key', $childKey)->exists() ? null : $childKey,
                'label_en' => $child['label_en'],
                'label_bn' => $child['label_bn'],
                'target' => $child['target'],
                'fragment' => $child['fragment'] ?? null,
                'visibility' => 'all',
                'is_active' => true,
                'sort_order' => $position += 10,
            ]);
        }

        $this->settings->set($group, $key, (string) $item->id, 'string', false);
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
    public static function children(): array
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
