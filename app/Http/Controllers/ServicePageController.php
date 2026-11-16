<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\ServiceDestination;
use App\Services\Integrations\ProviderStatus;
use App\Services\Travel\ServiceCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Dedicated page per service from {@see ServiceCatalog}. Actions go to the existing
 * enquiry or consultation forms; API-dependent parts show their real provider status.
 */
class ServicePageController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('travel-services.index', status: 301);
    }

    public function show(string $slug, ServiceCatalog $catalog, ProviderStatus $status): View
    {
        $service = $catalog->find($slug) ?? abort(404);

        $countries = isset($service['countries'])
            ? ServiceDestination::query()->where('service', $service['countries'])->where('is_active', true)
                ->with('country:id,name,iso2')->orderBy('sort_order')->get()->pluck('country')->filter()->values()
            : collect();

        $apis = collect($service['apis'] ?? [])->mapWithKeys(fn (string $capability): array => [$capability => $status->of($capability)]);
        $pageState = ($service['mode'] ?? 'enquiry') === 'provider'
            ? $apis->get($service['capability'], ProviderStatus::WAITING)
            : ProviderStatus::ENQUIRY;

        $enquiryUrl = isset($service['enquiry'])
            ? route('service-enquiries.create', array_filter(['service' => $service['enquiry'], 'category' => $service['category'] ?? null]))
            : null;

        $cmsEntry = isset($service['cms'])
            ? ContentEntry::query()->published(ContentType::BusinessService)->where('slug', $service['cms'])->first()
            : null;

        return view('services.show', [
            'slug' => $slug,
            'service' => $service,
            'countries' => $countries,
            'apis' => $apis,
            'pageState' => $pageState,
            'enquiryUrl' => $enquiryUrl,
            'cmsEntry' => $cmsEntry,
            'related' => collect($catalog->all())->filter(fn (array $item, string $key): bool => $key !== $slug && $item['group'] === $service['group'])->take(4),
        ]);
    }
}
