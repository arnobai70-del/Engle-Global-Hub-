<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's language. A "?lang=" query parameter wins (so each
 * language has a crawlable URL) and is remembered in the session.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys((array) config('localization.supported', []));
        $requested = $request->query('lang');

        if (is_string($requested) && in_array($requested, $supported, true)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale');
        app()->setLocale(is_string($locale) && in_array($locale, $supported, true) ? $locale : (string) config('app.locale', 'en'));

        return $next($request);
    }
}
