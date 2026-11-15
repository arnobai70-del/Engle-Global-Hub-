<?php

namespace App\Services;

use Illuminate\Database\QueryException;

/**
 * Admin-editable page titles and meta descriptions, stored as settings in the
 * "seo" group as "<route name>.title" and "<route name>.description". A blank
 * value keeps the page's own default.
 */
class SeoMetadata
{
    /**
     * Public pages whose metadata staff can override.
     *
     * @var list<string>
     */
    public const MANAGED_ROUTES = [
        'home', 'about', 'support', 'terms', 'work-visa.index', 'consultancy.singapore',
        'explore-bangladesh.index', 'offers.index', 'reviews.index',
    ];

    public function __construct(private SettingService $settings) {}

    /**
     * @return array{title?: string, description?: string}
     */
    public function forRoute(?string $routeName): array
    {
        if ($routeName === null || ! in_array($routeName, self::MANAGED_ROUTES, true)) {
            return [];
        }

        try {
            // One cached group read instead of two separate lookups per page.
            $seo = $this->settings->group('seo');
            $title = $seo[$routeName.'.title'] ?? null;
            $description = $seo[$routeName.'.description'] ?? null;
        } catch (QueryException) {
            // Settings storage unavailable (for example before migrations): keep page defaults.
            return [];
        }

        return array_filter([
            'title' => $this->clean($title, 70),
            'description' => $this->clean($description, 170),
        ], fn (?string $value): bool => $value !== null);
    }

    private function clean(mixed $value, int $limit): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');

        return $value === '' ? null : mb_substr($value, 0, $limit);
    }
}
