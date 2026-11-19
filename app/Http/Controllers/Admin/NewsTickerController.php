<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateNewsTickerSettingsRequest;
use App\Http\Requests\Admin\UpsertNewsTickerItemRequest;
use App\Models\HomepageBlock;
use App\Services\ActivityRecorder;
use App\Services\NewsTicker;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin › News ticker: headlines that scroll across the top of the homepage hero, and the
 * ticker settings. Headlines are homepage blocks in the "news" section.
 */
class NewsTickerController extends Controller
{
    public function __construct(private NewsTicker $ticker, private ActivityRecorder $activity) {}

    public function index(SettingService $settings): View
    {
        return view('admin.news-ticker.index', [
            'items' => $this->items(),
            'settings' => $settings->group('homepage'),
            'activeCount' => count($this->ticker->active()),
            'enabled' => $this->ticker->isEnabled(),
            'speed' => $this->ticker->speedKey(),
        ]);
    }

    public function create(): View
    {
        return view('admin.news-ticker.form', ['item' => new HomepageBlock(['is_active' => true])]);
    }

    public function store(UpsertNewsTickerItemRequest $request): RedirectResponse
    {
        $item = HomepageBlock::query()->create([
            ...$this->payload($request->validated()),
            'section' => NewsTicker::SECTION,
            'sort_order' => (int) HomepageBlock::query()->where('section', NewsTicker::SECTION)->max('sort_order') + 10,
        ]);
        $this->saved($request, 'news_ticker.item_created', $item);

        return redirect()->route('admin.news-ticker.index')->with('status', 'Headline added.');
    }

    public function edit(HomepageBlock $newsItem): View
    {
        $this->ensureNews($newsItem);

        return view('admin.news-ticker.form', ['item' => $newsItem]);
    }

    public function update(UpsertNewsTickerItemRequest $request, HomepageBlock $newsItem): RedirectResponse
    {
        $this->ensureNews($newsItem);
        $newsItem->update($this->payload($request->validated()));
        $this->saved($request, 'news_ticker.item_updated', $newsItem);

        return redirect()->route('admin.news-ticker.index')->with('status', 'Headline saved.');
    }

    public function destroy(Request $request, HomepageBlock $newsItem): RedirectResponse
    {
        $this->ensureNews($newsItem);
        $newsItem->delete();
        $this->saved($request, 'news_ticker.item_deleted', null, ['text' => $newsItem->title]);

        return redirect()->route('admin.news-ticker.index')->with('status', 'Headline deleted.');
    }

    public function move(Request $request, HomepageBlock $newsItem): RedirectResponse
    {
        $this->ensureNews($newsItem);
        $direction = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]])['direction'];
        $items = $this->items()->values();
        $index = $items->search(fn (HomepageBlock $item): bool => $item->is($newsItem));
        $other = $items->get($direction === 'up' ? $index - 1 : $index + 1);
        if ($other !== null) {
            DB::transaction(function () use ($items, $index, $other): void {
                $items->each(fn (HomepageBlock $item, int $position) => $item->sort_order === ($position + 1) * 10 ? null : $item->forceFill(['sort_order' => ($position + 1) * 10])->save());
                $current = $items->get($index);
                [$current->sort_order, $other->sort_order] = [$other->sort_order, $current->sort_order];
                $current->save();
                $other->save();
            });
            $this->saved($request, 'news_ticker.reordered', $newsItem, ['direction' => $direction]);
        }

        return redirect()->route('admin.news-ticker.index')->with('status', 'Order saved.');
    }

    public function updateSettings(UpdateNewsTickerSettingsRequest $request, SettingService $settings): RedirectResponse
    {
        $data = $request->validated();
        $settings->set('homepage', 'news_enabled', $request->boolean('news_enabled') ? '1' : '0', 'string', true);
        $settings->set('homepage', 'news_speed', $data['news_speed'], 'string', true);
        $settings->set('homepage', 'news_label', trim((string) ($data['news_label'] ?? '')), 'string', true);
        $settings->set('homepage', 'news_label_bn', trim((string) ($data['news_label_bn'] ?? '')), 'string', true);
        $this->saved($request, 'news_ticker.settings_updated', null, ['enabled' => $request->boolean('news_enabled'), 'speed' => $data['news_speed']]);

        return redirect()->route('admin.news-ticker.index')->with('status', 'Ticker settings saved.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        // The form uses office time (Asia/Dhaka); the database stores the app timezone.
        $time = fn (?string $value): ?Carbon => filled($value) ? Carbon::parse($value, NewsTicker::TIMEZONE)->setTimezone(config('app.timezone')) : null;

        return [
            'title' => $data['text'],
            'subtitle' => $data['text_bn'] ?? null,
            'url' => $data['url'] ?? null,
            'cta_label' => $data['badge'] ?? null,
            'starts_at' => $time($data['starts_at'] ?? null),
            'ends_at' => $time($data['ends_at'] ?? null),
            'is_active' => (bool) ($data['is_published'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function saved(Request $request, string $action, ?HomepageBlock $item, array $properties = []): void
    {
        $this->ticker->forget();
        $this->activity->record($request->user(), $action, $item, $properties);
    }

    private function ensureNews(HomepageBlock $item): void
    {
        abort_unless($item->section === NewsTicker::SECTION, 404);
    }

    /** @return Collection<int, HomepageBlock> */
    private function items()
    {
        return HomepageBlock::query()->where('section', NewsTicker::SECTION)->orderBy('sort_order')->orderBy('id')->get();
    }
}
