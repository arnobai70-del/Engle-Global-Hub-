<?php

namespace App\Http\Middleware;

use App\Services\Content\PublicPageAvailability;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends visitors to the homepage when a listing page has nothing published yet,
 * instead of showing an empty page. The redirect is temporary (302), so the page
 * works again automatically as soon as an admin publishes the first item.
 */
class RedirectWhenPageIsEmpty
{
    public function __construct(private PublicPageAvailability $availability) {}

    /**
     * @param  string|null  $who  "guests" redirects only signed-out visitors, for pages where
     *                            signed-in customers create the first content (reviews).
     */
    public function handle(Request $request, Closure $next, ?string $who = null): Response
    {
        $routeName = (string) $request->route()?->getName();
        if ($who === 'guests' && $request->user() !== null) {
            return $next($request);
        }

        if ($routeName !== '' && ! $this->availability->isAvailable($routeName)) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
