<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use JsonException;

class SettingService
{
    private const CACHE_PREFIX = 'settings:';

    private const CACHE_TTL_SECONDS = 3600;

    private const GROUP_CACHE_PREFIX = 'settings:group:';

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $cached = Cache::remember(
            $this->cacheKey($group, $key),
            self::CACHE_TTL_SECONDS,
            function () use ($group, $key): ?array {
                $setting = Setting::query()->where('group', $group)->where('key', $key)->first();
                return $setting ? ['value' => $setting->value, 'type' => $setting->type] : null;
            }
        );

        return $cached === null ? $default : $this->castValue($cached['value'], $cached['type']);
    }

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        return Cache::remember(
            self::GROUP_CACHE_PREFIX.$group,
            self::CACHE_TTL_SECONDS,
            fn (): array => Setting::query()
                ->where('group', $group)
                ->orderBy('key')
                ->get()
                ->mapWithKeys(fn (Setting $setting): array => [
                    $setting->key => $this->castValue($setting->value, $setting->type),
                ])
                ->all(),
        );
    }

    public function set(string $group, string $key, mixed $value, string $type = 'string', bool $isPublic = false): Setting
    {
        $type = strtolower($type);
        $this->validateType($type);

        $setting = Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $this->prepareValueForStorage($value, $type), 'type' => $type, 'is_public' => $isPublic]
        );

        $this->forget($group, $key);
        return $setting->refresh();
    }

    public function exists(string $group, string $key): bool
    {
        return Cache::remember(
            $this->existsCacheKey($group, $key),
            self::CACHE_TTL_SECONDS,
            fn (): bool => Setting::query()->where('group', $group)->where('key', $key)->exists(),
        );
    }

    public function forget(string $group, string $key): void
    {
        Cache::forget($this->cacheKey($group, $key));
        Cache::forget($this->existsCacheKey($group, $key));
        Cache::forget(self::GROUP_CACHE_PREFIX.$group);
    }

    public function delete(string $group, string $key): bool
    {
        $deleted = Setting::query()->where('group', $group)->where('key', $key)->delete();
        $this->forget($group, $key);
        return $deleted > 0;
    }

    private function cacheKey(string $group, string $key): string
    {
        return self::CACHE_PREFIX.$group.'.'.$key;
    }

    private function existsCacheKey(string $group, string $key): string
    {
        return self::CACHE_PREFIX.'exists.'.$group.'.'.$key;
    }

    private function validateType(string $type): void
    {
        if (! in_array($type, ['string', 'integer', 'float', 'boolean', 'json'], true)) {
            throw new InvalidArgumentException("Unsupported setting type [{$type}].");
        }
    }

    /** @throws JsonException */
    private function prepareValueForStorage(mixed $value, string $type): ?string
    {
        if ($value === null) return null;

        return match ($type) {
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            'boolean' => $this->normalizeBoolean($value) ? '1' : '0',
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    /** @throws JsonException */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) return null;

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true),
            'json' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            default => $value,
        };
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value)) return $value === 1;
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['1', 'true', 'yes', 'on'], true)) return true;
            if (in_array($value, ['0', 'false', 'no', 'off', ''], true)) return false;
        }
        throw new InvalidArgumentException('Invalid boolean setting value.');
    }
}
