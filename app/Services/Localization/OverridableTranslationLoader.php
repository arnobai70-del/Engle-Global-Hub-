<?php

namespace App\Services\Localization;

use App\Models\TranslationOverride;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/**
 * Wraps the file loader so JSON translations edited in the admin panel take
 * precedence over lang/*.json, without anyone editing files on the server.
 */
class OverridableTranslationLoader implements Loader
{
    public function __construct(private Loader $files) {}

    /**
     * @return array<string, string>
     */
    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->files->load($locale, $group, $namespace);

        if ($group !== '*' || $namespace !== '*') {
            return $lines;
        }

        return array_merge($lines, self::overrides($locale));
    }

    public function addNamespace($namespace, $hint)
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->files->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->files->namespaces();
    }

    /**
     * @return array<string, string>
     */
    public static function overrides(string $locale): array
    {
        try {
            return Cache::rememberForever(self::cacheKey($locale), fn (): array => TranslationOverride::query()
                ->where('locale', $locale)
                ->pluck('value', 'source')
                ->all());
        } catch (QueryException) {
            return [];
        }
    }

    public static function forget(string $locale): void
    {
        Cache::forget(self::cacheKey($locale));
    }

    private static function cacheKey(string $locale): string
    {
        return 'translation_overrides.'.$locale;
    }
}
