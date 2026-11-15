<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TranslationOverride;
use App\Services\ActivityRecorder;
use App\Services\Localization\OverridableTranslationLoader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Lets content staff correct interface translations. Only keys that already
 * exist in lang/<locale>.json can be edited, so no new UI text appears.
 */
class TranslationController extends Controller
{
    public function index(Request $request): View
    {
        $locale = $this->locale($request->query('locale'));
        $search = trim((string) $request->query('search', ''));
        $overrides = OverridableTranslationLoader::overrides($locale);

        $rows = collect($this->fileLines($locale))
            ->map(fn (string $value, string $source): array => [
                'source' => $source,
                'file' => $value,
                'override' => $overrides[$source] ?? null,
                'hash' => hash('sha256', $source),
            ])
            ->when($search !== '', fn ($rows) => $rows->filter(fn (array $row): bool => str_contains(mb_strtolower($row['source'].' '.$row['file'].' '.$row['override']), mb_strtolower($search))))
            ->values();

        return view('admin.translations.index', [
            'rows' => $rows,
            'locale' => $locale,
            'locales' => (array) config('localization.supported'),
            'search' => $search,
        ]);
    }

    public function update(Request $request, ActivityRecorder $activity): RedirectResponse
    {
        $locales = array_keys((array) config('localization.supported'));
        $data = $request->validate([
            'locale' => ['required', Rule::in($locales)],
            'source_hash' => ['required', 'string', 'size:64'],
            'value' => ['nullable', 'string', 'max:2000'],
        ]);

        $source = collect(array_keys($this->fileLines($data['locale'])))
            ->first(fn (string $source): bool => hash_equals(hash('sha256', $source), $data['source_hash']));
        abort_if($source === null, 404);

        $value = trim((string) ($data['value'] ?? ''));
        if ($value === '') {
            TranslationOverride::query()->where('locale', $data['locale'])->where('source_hash', $data['source_hash'])->delete();
        } else {
            TranslationOverride::query()->updateOrCreate(
                ['locale' => $data['locale'], 'source_hash' => $data['source_hash']],
                ['source' => $source, 'value' => strip_tags($value), 'updated_by_user_id' => $request->user()->getKey()],
            );
        }

        OverridableTranslationLoader::forget($data['locale']);
        $activity->record($request->user(), 'translation.updated', null, ['locale' => $data['locale'], 'reset' => $value === '']);

        return back()->with('status', $value === '' ? 'Translation reset to the default.' : 'Translation saved.');
    }

    private function locale(mixed $requested): string
    {
        $locales = array_keys((array) config('localization.supported'));

        return is_string($requested) && in_array($requested, $locales, true) ? $requested : 'bn';
    }

    /**
     * @return array<string, string>
     */
    private function fileLines(string $locale): array
    {
        // English has no translation file: its default text is the key itself,
        // taken from the Bengali file which lists every interface phrase.
        if ($locale === 'en') {
            $keys = array_keys($this->fileLines('bn'));

            return array_combine($keys, $keys);
        }

        $path = lang_path($locale.'.json');
        $lines = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];

        return is_array($lines) ? $lines : [];
    }
}
