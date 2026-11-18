<?php

namespace App\Services\Navigation;

use App\Models\MenuItem;
use App\Models\User;
use App\Services\Content\PublicPageAvailability;
use App\Services\Feature\FeatureManager;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Builds the visible menu for the current visitor from admin-managed menu items.
 * Items are hidden when inactive, switched off by a feature, not meant for the
 * visitor's role, or when their link cannot be resolved, so a broken link is never shown.
 */
class MenuService
{
    /** @var array<string, list<array<string, mixed>>|null> */
    private array $cache = [];

    public function __construct(private MenuTargets $targets, private FeatureManager $features, private PublicPageAvailability $availability) {}

    /**
     * Visible items for a location, or null when the location has no managed items
     * (the built-in navigation is used instead).
     *
     * @return list<array{key: ?string, label: string, url: ?string, new_tab: bool, icon: ?string, description: ?string, children: list<array<string, mixed>>}>|null
     */
    public function tree(string $location): ?array
    {
        $user = Auth::user() instanceof User ? Auth::user() : null;
        $key = $location.'|'.($user?->getKey() ?? 'guest').'|'.app()->getLocale();
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        try {
            $items = MenuItem::query()->where('location', $location)->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->where('is_active', true)])
                ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
            $hasAny = MenuItem::query()->where('location', $location)->exists();
        } catch (Throwable) {
            return $this->cache[$key] = null;
        }

        if (! $hasAny) {
            return $this->cache[$key] = null;
        }

        // Pass 1: visible top-level items, without repeats of an earlier item's link or label.
        $seen = ['urls' => [], 'labels' => [], 'links' => []];
        $nodes = [];
        foreach ($items as $item) {
            if (! $this->visible($item, $user)) {
                continue;
            }
            $children = [];
            foreach ($item->children as $child) {
                if ($this->visible($child, $user) && ($childNode = $this->node($child)) !== null && $childNode['url'] !== null) {
                    $children[] = $childNode;
                }
            }
            $node = $this->node($item, $children);
            if ($node !== null && ($node['url'] !== null || $children !== []) && ! $this->repeatsEarlier($node, $seen)) {
                $nodes[] = $node;
            }
        }

        // Top-level links come first, so a dropdown never repeats one of them.
        foreach ($nodes as $node) {
            if ($node['children'] === []) {
                $this->isNewLink($node['url'], $seen);
            }
        }

        // Pass 2: a link already shown in this menu (in another dropdown or column) is not shown again.
        $tree = [];
        foreach ($nodes as $node) {
            if ($node['children'] !== []) {
                $children = [];
                foreach ($node['children'] as $child) {
                    if ($this->isNewLink($child['url'], $seen)) {
                        $children[] = $child;
                    }
                }
                if ($children === [] && $node['url'] === null) {
                    continue;
                }
                $node['children'] = $children;
            }
            $tree[] = $node;
        }

        return $this->cache[$key] = $tree;
    }

    /**
     * Remembers a rendered link; false when the same link was already rendered in this menu.
     *
     * @param  array{urls: array<string, true>, labels: array<string, true>, links: array<string, true>}  $seen
     */
    private function isNewLink(?string $url, array &$seen): bool
    {
        if ($url === null) {
            return true;
        }
        $link = strtolower(rtrim($url, '/'));
        if (isset($seen['links'][$link])) {
            return false;
        }
        $seen['links'][$link] = true;

        return true;
    }

    /**
     * Defensive: a top-level item that repeats an earlier one's link or label is not shown twice
     * (for example two "Explore Bangladesh" items after an import or an old seeder run).
     *
     * @param  array{url: ?string, label: string}  $node
     * @param  array{urls: array<string, true>, labels: array<string, true>, links: array<string, true>}  $seen
     */
    private function repeatsEarlier(array $node, array &$seen): bool
    {
        $url = $node['url'] !== null ? strtolower(rtrim($node['url'], '/')) : null;
        $label = mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $node['label'])));
        if (($url !== null && isset($seen['urls'][$url])) || ($label !== '' && isset($seen['labels'][$label]))) {
            return true;
        }
        if ($url !== null) {
            $seen['urls'][$url] = true;
        }
        $seen['labels'][$label] = true;

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @return array{key: ?string, label: string, url: ?string, new_tab: bool, icon: ?string, description: ?string, children: list<array<string, mixed>>}|null
     */
    private function node(MenuItem $item, array $children = []): ?array
    {
        $url = $this->targets->url($item->target, $item->fragment);
        if ($item->target !== null && $url === null) {
            return null;
        }

        return [
            'key' => $item->key,
            'label' => $item->label(),
            'url' => $url,
            'new_tab' => $item->open_in_new_tab && str_starts_with((string) $item->target, 'url:'),
            'icon' => $item->icon,
            'description' => $item->description(),
            'children' => $children,
        ];
    }

    private function visible(MenuItem $item, ?User $user): bool
    {
        $routeName = $this->targets->routeName($item->target);
        if ($routeName !== null && ! $this->availability->isAvailable($routeName)) {
            return false;
        }
        foreach (array_filter([$item->feature, $this->targets->featureFor($item->target)]) as $feature) {
            if (! $this->features->isVisibleTo($feature, $user)) {
                return false;
            }
        }

        return match ($item->visibility) {
            'guests' => $user === null,
            'auth' => $user !== null,
            'roles' => $user !== null && $user->hasAnyRole($item->roles ?? []),
            default => true,
        };
    }

    public function forget(): void
    {
        $this->cache = [];
    }
}
