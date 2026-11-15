<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies admin-managed redirects to public GET/HEAD requests.
 */
class ApplyRedirects
{
    public const CACHE_KEY = 'redirects.active';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');
        $target = self::map()[$path] ?? null;

        if ($target === null) {
            return $next($request);
        }

        Redirect::query()->where('from_path', $path)->increment('hits', 1, ['last_hit_at' => now()]);

        return redirect()->to($target['to'], $target['code']);
    }

    /**
     * @return array<string, array{to: string, code: int}>
     */
    public static function map(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn (): array => Redirect::query()->where('is_active', true)
                ->get(['from_path', 'to_url', 'status_code'])
                ->mapWithKeys(fn (Redirect $redirect): array => [$redirect->from_path => ['to' => $redirect->to_url, 'code' => $redirect->status_code]])
                ->all());
        } catch (QueryException) {
            return [];
        }
    }
}
