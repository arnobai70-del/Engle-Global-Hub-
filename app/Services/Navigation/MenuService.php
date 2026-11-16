<?php

namespace App\Services\Navigation;

use App\Models\MenuItem;
use App\Models\User;
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

    public function __construct(private MenuTargets $targets, private FeatureManager $features) {}

    /**
     * Visible items for a location, or null when the location has no managed items
     * (the built-in navigation is used instead).
     *
     * @return list<array{label: string, url: ?string, new_tab: bool, icon: ?string, children: list<array<string, mixed>>}>|null
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

        $tree = [];
        foreach ($items as $item) {
            if (! $this->visible($item, $user)) {
                continue;
            }
            $children = [];
            foreach ($item->children as $child) {
                if ($this->visible($child, $user) && ($node = $this->node($child)) !== null && $node['url'] !== null) {
                    $children[] = $node;
                }
            }
            $node = $this->node($item, $children);
            if ($node !== null && ($node['url'] !== null || $children !== [])) {
                $tree[] = $node;
            }
        }

        return $this->cache[$key] = $tree;
    }

    /**
     * @param  list<array<string, mixed>>  $children
     * @return array{label: string, url: ?string, new_tab: bool, icon: ?string, children: list<array<string, mixed>>}|null
     */
    private function node(MenuItem $item, array $children = []): ?array
    {
        $url = $this->targets->url($item->target, $item->fragment);
        if ($item->target !== null && $url === null) {
            return null;
        }

        return [
            'label' => $item->label(),
            'url' => $url,
            'new_tab' => $item->open_in_new_tab && str_starts_with((string) $item->target, 'url:'),
            'icon' => $item->icon,
            'children' => $children,
        ];
    }

    private function visible(MenuItem $item, ?User $user): bool
    {
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
