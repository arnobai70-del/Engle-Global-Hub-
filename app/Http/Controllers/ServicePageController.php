<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\ServicePageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Every service page uses one template (DESIGN_BRIEF.md "Service pages"): a quote
 * form saved to the CRM, WhatsApp, what is included, steps and documents.
 * Standalone pages (/visa, /hotels, /tours, /flights) switch to their online search
 * only after the provider has passed its connection test.
 */
class ServicePageController extends Controller
{
    public function __construct(private ServicePageData $pages) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('travel-services.index', status: 301);
    }

    public function show(string $slug): View
    {
        $page = $this->pages->for($slug) ?? abort(404);
        $cmsEntry = $page['cms'] !== null
            ? ContentEntry::query()->published(ContentType::BusinessService)->where('slug', $page['cms'])->first()
            : null;

        return view('services.template', ['page' => $page, 'cmsEntry' => $cmsEntry]);
    }

    /**
     * /work-visa: guidance and enquiry only (work permits are issued by authorities).
     */
    public function workVisa(): View
    {
        return view('services.template', ['page' => $this->pages->for('work-visa')]);
    }

    /**
     * /flights: public enquiry page. Signed-in customers who may search get the online
     * flight search once the flight provider is live.
     */
    public function flights(Request $request, ProviderStatus $status): View
    {
        $canSearch = $request->user()?->can('flights.search') === true && $status->of('flights') === ProviderStatus::LIVE;

        return $canSearch
            ? view('flights.search')
            : view('services.template', ['page' => $this->pages->for('flights')]);
    }

    /**
     * Template for a standalone service whose provider is not live.
     */
    public function standalone(string $slug, ?string $liveSearchUrl = null): View
    {
        return view('services.template', ['page' => $this->pages->for($slug), 'liveSearchUrl' => $liveSearchUrl]);
    }
}
