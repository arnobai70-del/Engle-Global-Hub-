<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ApplyRedirects;
use App\Models\Redirect;
use App\Services\ActivityRecorder;
use App\Services\SeoMetadata;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * SEO workspace: page titles and descriptions, and URL redirects.
 */
class SeoController extends Controller
{
    /** Paths that must never be redirected away. */
    private const PROTECTED_PREFIXES = ['/admin', '/login', '/logout', '/register', '/up', '/sitemap.xml', '/robots.txt', '/build', '/storage'];

    public function __construct(private SettingService $settings, private ActivityRecorder $activity) {}

    public function index(): View
    {
        $pages = collect(SeoMetadata::MANAGED_ROUTES)->map(fn (string $route): array => [
            'route' => $route,
            'url' => route($route, absolute: false),
            'title' => (string) $this->settings->get('seo', $route.'.title', ''),
            'description' => (string) $this->settings->get('seo', $route.'.description', ''),
        ]);

        return view('admin.seo.index', [
            'pages' => $pages,
            'redirects' => Redirect::query()->orderBy('from_path')->get(),
        ]);
    }

    public function updatePage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'route' => ['required', Rule::in(SeoMetadata::MANAGED_ROUTES)],
            'title' => ['nullable', 'string', 'max:70'],
            'description' => ['nullable', 'string', 'max:170'],
        ]);

        foreach (['title', 'description'] as $field) {
            $this->settings->set('seo', $data['route'].'.'.$field, trim(strip_tags((string) ($data[$field] ?? ''))), 'string', true);
        }
        $this->activity->record($request->user(), 'seo.page_updated', null, ['route' => $data['route']]);

        return back()->with('status', 'SEO details saved for '.$data['route'].'.');
    }

    public function storeRedirect(Request $request): RedirectResponse
    {
        $request->merge(['from_path' => '/'.ltrim(trim((string) $request->input('from_path')), '/')]);
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500', 'regex:/^\/[A-Za-z0-9\-._~\/]*$/', Rule::unique('redirects', 'from_path')],
            'to_url' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! (is_string($value) && (preg_match('#^/(?!/)#', $value) === 1 || Str::startsWith($value, 'https://')))) {
                    $fail('The destination must start with "/" (this site) or "https://".');
                }
            }],
            'status_code' => ['required', Rule::in([301, 302])],
        ]);

        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if ($data['from_path'] === $prefix || Str::startsWith($data['from_path'], $prefix.'/')) {
                return back()->withErrors(['from_path' => 'This path cannot be redirected.'])->withInput();
            }
        }
        if ($data['from_path'] === '/' || rtrim($data['to_url'], '/') === rtrim($data['from_path'], '/')) {
            return back()->withErrors(['to_url' => 'A redirect cannot point to itself or redirect the homepage.'])->withInput();
        }
        if (Redirect::query()->where('from_path', $data['to_url'])->where('is_active', true)->exists()) {
            return back()->withErrors(['to_url' => 'The destination is itself redirected. Point to the final URL to avoid a redirect chain.'])->withInput();
        }

        $redirect = Redirect::query()->create([...$data, 'is_active' => true]);
        $this->forget();
        $this->activity->record($request->user(), 'redirect.created', $redirect, ['from' => $redirect->from_path]);

        return back()->with('status', 'Redirect added.');
    }

    public function updateRedirect(Request $request, Redirect $redirect): RedirectResponse
    {
        $redirect->update(['is_active' => $request->boolean('is_active')]);
        $this->forget();
        $this->activity->record($request->user(), 'redirect.updated', $redirect, ['active' => $redirect->is_active]);

        return back()->with('status', 'Redirect updated.');
    }

    public function destroyRedirect(Request $request, Redirect $redirect): RedirectResponse
    {
        $this->activity->record($request->user(), 'redirect.deleted', $redirect, ['from' => $redirect->from_path]);
        $redirect->delete();
        $this->forget();

        return back()->with('status', 'Redirect removed.');
    }

    private function forget(): void
    {
        Cache::forget(ApplyRedirects::CACHE_KEY);
    }
}
