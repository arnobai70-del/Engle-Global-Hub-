<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\Offer;
use App\Services\Feature\FeatureManager;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(FeatureManager $features): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('terms'), 'priority' => '0.5'],
        ];

        foreach ([
            'about' => ['route' => 'about', 'priority' => '0.7'],
            'support' => ['route' => 'support', 'priority' => '0.6'],
            'visa' => ['route' => 'work-visa.index', 'priority' => '0.7'],
            'student-consultancy' => ['route' => 'consultancy.singapore', 'priority' => '0.9'],
        ] as $feature => $page) {
            if ($features->isVisibleTo($feature, null)) {
                $urls[] = [
                    'loc' => route($page['route']),
                    'priority' => $page['priority'],
                ];
            }
        }

        $urls[] = ['loc' => route('reviews.index'), 'priority' => '0.4'];

        $published = ContentEntry::query()->where('status', ContentEntry::STATUS_PUBLISHED)
            ->whereNotIn('type', [ContentType::Faq->value, ContentType::PageSection->value])->get(['type', 'slug', 'updated_at', 'status', 'canonical_url']);
        foreach (['company' => ContentType::CompanyProfile, 'business-services' => ContentType::BusinessService, 'restaurants' => ContentType::Restaurant, 'inspiration' => ContentType::Inspiration, 'success-stories' => ContentType::SuccessStory] as $listing => $type) {
            if ($published->contains(fn (ContentEntry $entry): bool => $entry->type === $type)) {
                $urls[] = ['loc' => route($listing.'.index'), 'priority' => '0.5'];
            }
        }
        foreach ($published as $entry) {
            // Entries that declare another canonical URL are left to that URL.
            if ($entry->canonical_url === null && ($url = $entry->publicUrl()) !== null) {
                $urls[] = ['loc' => $url, 'priority' => $entry->type === ContentType::Guide ? '0.8' : '0.5', 'lastmod' => $entry->updated_at?->toAtomString()];
            }
        }

        if (Offer::query()->current()->exists()) {
            $urls[] = ['loc' => route('offers.index'), 'priority' => '0.6'];
        }

        $publishedDestinations = Destination::query()->published()
            ->whereHas('country', fn ($query) => $query->where('iso2', 'BD'))
            ->get(['slug', 'updated_at']);

        if ($publishedDestinations->isNotEmpty()) {
            $urls[] = ['loc' => route('explore-bangladesh.index'), 'priority' => '0.6'];

            foreach ($publishedDestinations as $destination) {
                $urls[] = [
                    'loc' => route('explore-bangladesh.show', $destination->slug),
                    'priority' => '0.5',
                    'lastmod' => $destination->updated_at?->toAtomString(),
                ];
            }
        }

        return response(
            view('seo.sitemap', ['urls' => $urls])->render(),
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ],
        );
    }
}
