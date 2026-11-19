<?php

namespace App\Services;

use App\Models\HomepageBlock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * The homepage news ticker (Admin › News ticker). Settings stay in the existing "homepage"
 * settings group (news_enabled, news_label …); headlines are homepage blocks in the "news"
 * section: title = English text, subtitle = Bengali text, url = link, cta_label = badge,
 * is_active = published, starts_at / ends_at = optional show window.
 */
class NewsTicker
{
    public const SECTION = 'news';

    /** Admins enter and read the show window in office time; it is stored in the app timezone. */
    public const TIMEZONE = 'Asia/Dhaka';

    /** Reading speed in pixels per second. */
    public const SPEEDS = ['slow' => 35, 'normal' => 55, 'fast' => 80];

    public const CACHE_KEY = 'homepage:news-ticker:items';

    private const CACHE_SECONDS = 300;

    public function __construct(private SettingService $settings) {}

    public function isEnabled(): bool
    {
        $value = $this->setting('news_enabled', '0');

        return ! in_array(strtolower(trim((string) $value)), ['', '0', 'false', 'off', 'no'], true);
    }

    public function speedKey(): string
    {
        $speed = (string) $this->setting('news_speed', 'normal');

        return array_key_exists($speed, self::SPEEDS) ? $speed : 'normal';
    }

    public function speed(): int
    {
        return self::SPEEDS[$this->speedKey()];
    }

    /** The "Latest" label in the visitor's language. */
    public function label(): string
    {
        $english = trim((string) $this->setting('news_label', ''));
        $bengali = trim((string) $this->setting('news_label_bn', ''));

        return app()->getLocale() === 'bn' && $bengali !== '' ? $bengali : ($english !== '' ? $english : __('Latest'));
    }

    /**
     * Published headlines (any window), cached briefly; the window is checked on every request.
     *
     * @return list<array{id: int, text: string, text_bn: ?string, url: ?string, badge: ?string, starts_at: ?int, ends_at: ?int}>
     */
    public function published(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): array => HomepageBlock::query()
                ->where('section', self::SECTION)->where('is_active', true)
                ->orderBy('sort_order')->orderBy('id')->get()
                ->map(fn (HomepageBlock $item): array => [
                    'id' => (int) $item->id,
                    'text' => (string) $item->title,
                    'text_bn' => filled($item->subtitle) ? (string) $item->subtitle : null,
                    'url' => filled($item->url) ? (string) $item->url : null,
                    'badge' => filled($item->cta_label) ? (string) $item->cta_label : null,
                    'starts_at' => $item->starts_at?->getTimestamp(),
                    'ends_at' => $item->ends_at?->getTimestamp(),
                ])->filter(fn (array $item): bool => $item['text'] !== '')->values()->all());
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Headlines to show now, in the visitor's language (Bengali falls back to English).
     *
     * @return list<array{text: string, url: ?string, external: bool, badge: ?string}>
     */
    public function active(?Carbon $now = null): array
    {
        $time = ($now ?? now())->getTimestamp();

        return collect($this->published())
            ->filter(fn (array $item): bool => ($item['starts_at'] === null || $item['starts_at'] <= $time) && ($item['ends_at'] === null || $item['ends_at'] > $time))
            ->map(fn (array $item): array => [
                'text' => app()->getLocale() === 'bn' && $item['text_bn'] !== null ? $item['text_bn'] : $item['text'],
                'url' => $item['url'],
                'external' => $item['url'] !== null && str_starts_with($item['url'], 'https://'),
                'badge' => $item['badge'],
            ])->values()->all();
    }

    /**
     * Everything the homepage needs, or null when the ticker must not render at all.
     *
     * @return array{label: string, speed: int, items: list<array{text: string, url: ?string, external: bool, badge: ?string}>}|null
     */
    public function forHomepage(): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }
        $items = $this->active();

        return $items === [] ? null : ['label' => $this->label(), 'speed' => $this->speed(), 'items' => $items];
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function setting(string $key, mixed $default): mixed
    {
        try {
            return $this->settings->get('homepage', $key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
