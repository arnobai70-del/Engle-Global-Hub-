<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\ActivityRecorder;
use App\Services\Content\HomepageServiceCards;
use App\Services\Navigation\MenuTargets;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Admin › Menus: header and footer menus, homepage service tiles and the header button.
 * Every link is validated: internal targets come from a vetted list and external
 * links must be https, so a broken or unsafe link cannot be saved.
 */
class MenuItemController extends Controller
{
    public function __construct(private MenuTargets $targets, private ActivityRecorder $activity) {}

    public function index(Request $request, SettingService $settings, HomepageServiceCards $cards): View
    {
        $location = array_key_exists((string) $request->query('location'), MenuItem::LOCATIONS) ? (string) $request->query('location') : 'header';
        $items = MenuItem::query()->where('location', $location)->whereNull('parent_id')->with('children')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.menus.index', [
            'location' => $location,
            'items' => $items,
            'tileWarnings' => $location === 'home_tiles' ? $cards->adminWarnings($items->where('is_active', true)->values()) : [],
            'counts' => MenuItem::query()->selectRaw('location, count(*) as total')->groupBy('location')->pluck('total', 'location'),
            'targets' => $this->targets,
            'branding' => rescue(fn () => $settings->group('branding'), [], false),
        ]);
    }

    public function create(Request $request): View
    {
        $location = array_key_exists((string) $request->query('location'), MenuItem::LOCATIONS) ? (string) $request->query('location') : abort(404);

        return view('admin.menus.form', $this->formData(new MenuItem(['location' => $location, 'visibility' => 'all', 'is_active' => true, 'sort_order' => (MenuItem::query()->where('location', $location)->max('sort_order') ?? 0) + 10])));
    }

    public function store(Request $request): RedirectResponse
    {
        $item = new MenuItem(['location' => (string) $request->input('location')]);
        abort_unless(array_key_exists($item->location, MenuItem::LOCATIONS), 422);
        $item->fill($this->validated($request, $item))->save();
        $this->activity->record($request->user(), 'menu.created', $item, ['location' => $item->location]);

        return redirect()->route('admin.menus.index', ['location' => $item->location])->with('status', 'Menu item added.');
    }

    public function edit(MenuItem $item): View
    {
        return view('admin.menus.form', $this->formData($item));
    }

    public function update(Request $request, MenuItem $item): RedirectResponse
    {
        $item->fill($this->validated($request, $item))->save();
        $this->activity->record($request->user(), 'menu.updated', $item, ['location' => $item->location]);

        return redirect()->route('admin.menus.index', ['location' => $item->location])->with('status', 'Menu item saved.');
    }

    public function toggle(Request $request, MenuItem $item): RedirectResponse
    {
        $item->forceFill(['is_active' => ! $item->is_active])->save();
        $this->activity->record($request->user(), 'menu.toggled', $item, ['active' => $item->is_active]);

        return back()->with('status', $item->is_active ? 'Menu item shown.' : 'Menu item hidden.');
    }

    public function move(Request $request, MenuItem $item): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];

        DB::transaction(function () use ($item, $direction): void {
            $siblings = MenuItem::query()->where('location', $item->location)->where('parent_id', $item->parent_id)
                ->orderBy('sort_order')->orderBy('id')->get()->values();
            $index = $siblings->search(fn (MenuItem $sibling): bool => $sibling->is($item));
            $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
            if ($index === false || ! $siblings->has($swapWith)) {
                return;
            }
            $ordered = $siblings->all();
            [$ordered[$index], $ordered[$swapWith]] = [$ordered[$swapWith], $ordered[$index]];
            foreach ($ordered as $position => $sibling) {
                $sibling->forceFill(['sort_order' => ($position + 1) * 10])->save();
            }
        });

        return back()->with('status', 'Order updated.');
    }

    public function destroy(Request $request, MenuItem $item): RedirectResponse
    {
        $location = $item->location;
        $this->activity->record($request->user(), 'menu.deleted', $item, ['location' => $location, 'label' => $item->label_en]);
        $item->delete();

        return redirect()->route('admin.menus.index', ['location' => $location])->with('status', 'Menu item deleted.');
    }

    public function branding(Request $request, SettingService $settings): RedirectResponse
    {
        $data = $request->validate([
            'header_tagline' => ['nullable', 'string', 'max:120'],
            'header_tagline_bn' => ['nullable', 'string', 'max:120'],
        ]);

        foreach ($data as $key => $value) {
            $settings->set('branding', $key, trim(strip_tags((string) $value)), 'string', true);
        }
        $this->activity->record($request->user(), 'branding.updated', null, ['keys' => array_keys($data)]);

        return back()->with('status', 'Branding saved. Leave a field empty to use the default text.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, MenuItem $item): array
    {
        $location = $item->location;
        $nested = in_array($location, MenuItem::NESTED_LOCATIONS, true);
        $roleNames = Role::query()->pluck('name')->all();

        $data = $request->validate([
            'label_en' => ['required', 'string', 'max:80'],
            'label_bn' => ['nullable', 'string', 'max:80'],
            'description_en' => ['nullable', 'string', 'max:300'],
            'description_bn' => ['nullable', 'string', 'max:300'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('location', $location)->whereNull('parent_id')],
            'link_type' => ['required', Rule::in($nested ? ['route', 'url', 'none'] : ['route', 'url'])],
            'route_target' => ['nullable', 'required_if:link_type,route', 'string', 'max:200'],
            'external_url' => ['nullable', 'required_if:link_type,url', 'string', 'max:290'],
            'fragment' => ['nullable', 'regex:/^[A-Za-z0-9_-]{1,60}$/'],
            'icon' => ['nullable', Rule::in(MenuItem::ICONS)],
            'feature' => ['nullable', Rule::in(array_keys(array_filter(config('features'), 'is_array')))],
            'visibility' => ['required', Rule::in(array_keys(MenuItem::VISIBILITIES))],
            'roles' => ['nullable', 'required_if:visibility,roles', 'array'],
            'roles.*' => ['string', Rule::in($roleNames)],
            'open_in_new_tab' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ], [
            'parent_id.exists' => 'Choose a top-level item of the same menu as the parent (menus have at most two levels).',
        ]);

        $parentId = $nested && filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;
        if ($item->exists && $parentId === $item->id) {
            throw ValidationException::withMessages(['parent_id' => 'An item cannot be its own parent.']);
        }
        if ($item->exists && $parentId !== null && $item->children()->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'This item has sub-items, so it must stay at the top level.']);
        }

        $target = match ($data['link_type']) {
            'route' => (string) $data['route_target'],
            'url' => 'url:'.trim((string) $data['external_url']),
            default => null,
        };
        if ($data['link_type'] === 'none' && $parentId !== null) {
            throw ValidationException::withMessages(['link_type' => 'Only a top-level item can be a heading without a link.']);
        }
        if ($target !== null && ! $this->targets->isAllowed($target)) {
            throw ValidationException::withMessages([
                $data['link_type'] === 'url' ? 'external_url' : 'route_target' => $data['link_type'] === 'url'
                    ? 'Enter a complete external link that starts with https://.'
                    : 'Choose a page from the list.',
            ]);
        }
        if ($location === 'home_tiles' && blank($data['icon'] ?? null)) {
            throw ValidationException::withMessages(['icon' => 'Choose an icon for the homepage tile.']);
        }

        $label = trim(strip_tags((string) $data['label_en']));
        $siblings = MenuItem::query()->where('location', $location)->where('parent_id', $parentId)
            ->when($item->exists, fn ($query) => $query->whereKeyNot($item->getKey()))->get();
        if ($siblings->contains(fn (MenuItem $sibling): bool => mb_strtolower($sibling->label_en) === mb_strtolower($label))) {
            throw ValidationException::withMessages(['label_en' => 'Another item at this level already uses this label.']);
        }
        $fragment = filled($data['fragment'] ?? null) ? (string) $data['fragment'] : null;
        if ($target !== null && $siblings->contains(fn (MenuItem $sibling): bool => $sibling->target === $target && $sibling->fragment === $fragment)) {
            throw ValidationException::withMessages([$data['link_type'] === 'url' ? 'external_url' : 'route_target' => 'Another item at this level already links to this page.']);
        }

        return [
            'parent_id' => $parentId,
            'label_en' => $label,
            'label_bn' => filled($data['label_bn'] ?? null) ? trim(strip_tags((string) $data['label_bn'])) : null,
            'description_en' => $location === 'home_tiles' && filled($data['description_en'] ?? null) ? trim(strip_tags((string) $data['description_en'])) : null,
            'description_bn' => $location === 'home_tiles' && filled($data['description_bn'] ?? null) ? trim(strip_tags((string) $data['description_bn'])) : null,
            'target' => $target,
            'fragment' => $fragment,
            'icon' => $data['icon'] ?? null,
            'feature' => $data['feature'] ?? null,
            'visibility' => $data['visibility'],
            'roles' => $data['visibility'] === 'roles' ? array_values($data['roles'] ?? []) : null,
            'open_in_new_tab' => $data['link_type'] === 'url' && (bool) ($data['open_in_new_tab'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) $data['sort_order'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MenuItem $item): array
    {
        return [
            'item' => $item,
            'parents' => in_array($item->location, MenuItem::NESTED_LOCATIONS, true)
                ? MenuItem::query()->where('location', $item->location)->whereNull('parent_id')->when($item->exists, fn ($query) => $query->whereKeyNot($item->getKey()))->orderBy('sort_order')->get()
                : collect(),
            'targetOptions' => $this->targets->options(),
            'features' => array_keys(array_filter(config('features'), 'is_array')),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ];
    }
}
