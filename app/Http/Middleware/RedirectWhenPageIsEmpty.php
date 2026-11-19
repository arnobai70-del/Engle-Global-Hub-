<?php

namespace App\Http\Middleware;

use App\Services\Content\PublicPageAvailability;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends visitors elsewhere when a page has nothing published yet, instead of showing an empty
 * page. The redirect is temporary (302), so the page works again automatically as soon as an
 * admin adds the content. The Study in Malaysia planning tools go back to Study in Malaysia
 * with a short notice; other pages go to the homepage.
 */
class RedirectWhenPageIsEmpty
{
    /** Pages whose visitors are better served by a parent page than by the homepage. */
    private const PARENTS = [
        'consultancy.malaysia.eligibility' => 'consultancy.malaysia',
        'consultancy.malaysia.cost-calculator' => 'consultancy.malaysia',
        'consultancy.malaysia.courses' => 'consultancy.malaysia',
        'appointments.book' => 'consultancy.malaysia',
    ];

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
            if (isset(self::PARENTS[$routeName])) {
                return redirect()->to(route(self::PARENTS[$routeName]).'#apply')
                    ->with('api_waiting', __('This tool opens soon. Until then, a consultant can help you directly: book a free consultation below.'));
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
