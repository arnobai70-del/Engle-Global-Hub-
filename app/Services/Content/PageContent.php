<?php

namespace App\Services\Content;

use App\Enums\ContentType;
use App\Models\ContactDirectoryEntry;
use App\Models\ContentEntry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Published admin content for existing pages. Every lookup returns nothing
 * when content is absent or storage is unavailable, so pages keep their
 * built-in text.
 */
class PageContent
{
    /** @var array<string, ContentEntry|null> */
    private array $sections = [];

    public function section(string $slug): ?ContentEntry
    {
        if (! array_key_exists($slug, $this->sections)) {
            $this->sections[$slug] = $this->safely(fn () => ContentEntry::query()
                ->where('type', ContentType::PageSection->value)->where('slug', $slug)
                ->where('status', ContentEntry::STATUS_PUBLISHED)->first());
        }

        return $this->sections[$slug];
    }

    /**
     * A published page section written as a bulleted list ("- Title: text" per line),
     * returned as cards in the visitor's language. Empty when the section is not published.
     *
     * @return list<array{title: string, text: string}>
     */
    public function points(string ...$slugs): array
    {
        foreach ($slugs as $slug) {
            $section = $this->section($slug);
            if ($section === null) {
                continue;
            }

            $points = collect(preg_split('/\R/', (string) $section->localized('body')) ?: [])
                ->map(fn (string $line): string => trim((string) preg_replace('/^[-*•]\s*/u', '', trim($line))))
                ->filter()
                ->map(function (string $line): array {
                    [$title, $text] = str_contains($line, ':') ? array_map('trim', explode(':', $line, 2)) : [$line, ''];

                    return ['title' => Str::ucfirst($title), 'text' => Str::ucfirst($text)];
                })
                ->values()
                ->all();

            if ($points !== []) {
                return $points;
            }
        }

        return [];
    }

    /**
     * @return Collection<int, ContentEntry>
     */
    public function published(ContentType $type, ?string $category = null): Collection
    {
        return $this->safely(fn () => ContentEntry::query()->published($type)
            ->when($category !== null, fn ($query) => $query->where('category', $category))->get()) ?? collect();
    }

    /**
     * @return Collection<int, ContactDirectoryEntry>
     */
    public function contacts(): Collection
    {
        return $this->safely(fn () => ContactDirectoryEntry::query()->visible()->get()) ?? collect();
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T|null
     */
    private function safely(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (QueryException) {
            return null;
        }
    }
}
