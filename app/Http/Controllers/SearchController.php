<?php

namespace App\Http\Controllers;

use App\Enums\BusinessService;
use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\Offer;
use App\Models\ServiceEnquiry;
use App\Services\Feature\FeatureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Site search over public content only: pages, services, published
 * destinations and current offers. Drafts and private records are never searched.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request, FeatureManager $features): View
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $results = mb_strlen($query) >= 2 ? $this->search($query, $features) : collect();

        return view('search.index', ['query' => $query, 'results' => $results]);
    }

    /**
     * @return Collection<int, array{title: string, summary: string, url: string, type: string}>
     */
    private function search(string $query, FeatureManager $features): Collection
    {
        $needle = mb_strtolower($query);
        $matches = fn (string ...$texts): bool => str_contains(mb_strtolower(implode(' ', $texts)), $needle);

        $pages = collect([
            ['consultancy.singapore', 'student-consultancy', __('Study in Singapore from Bangladesh'), __('Student consultancy, admission and Student\'s Pass guidance'), 'study singapore student visa admission university college pass consultation'],
            ['about', 'about', __('About Us'), __('About Eagle Global Hub LTD, mission, vision and team'), 'about company team mission vision'],
            ['support', 'support', __('Contact & Support'), __('Office address, phone and email'), 'contact support help phone email office address'],
            ['explore-bangladesh.index', null, __('Explore Bangladesh'), __('Tourist places in Bangladesh'), 'bangladesh tourism travel cox bazar sylhet kuakata'],
            ['offers.index', null, __('Special Offers'), __('Current promotions'), 'offers deals promotion discount holiday package'],
            ['reviews.index', null, __('Customer Reviews'), __('What our customers say'), 'reviews ratings testimonials'],
            ['work-visa.index', 'visa', __('Work Visa Processing'), __('Job visa and work permit information'), 'work job visa permit employment'],
        ])->filter(fn (array $page): bool => Route::has($page[0]) && ($page[1] === null || $features->isVisibleTo($page[1], request()->user())))
            ->filter(fn (array $page): bool => $matches($page[2], $page[3], $page[4]))
            ->map(fn (array $page): array => ['title' => $page[2], 'summary' => $page[3], 'url' => route($page[0]), 'type' => __('Page')]);

        $services = collect(ServiceEnquiry::SERVICES)
            ->map(fn (string $value) => BusinessService::from($value))
            ->filter(fn ($service): bool => $matches($service->label(), 'enquiry booking travel'))
            ->map(fn ($service): array => [
                'title' => __($service->label()).' — '.__('enquiry'),
                'summary' => __('Send an enquiry; our team replies with options and prices.'),
                'url' => route('service-enquiries.create', ServiceEnquiry::slugFor($service)),
                'type' => __('Service'),
            ]);

        $like = '%'.addcslashes($query, '%_\\').'%';
        $destinations = Destination::query()->published()
            ->whereHas('country', fn ($builder) => $builder->where('iso2', 'BD'))
            ->where(fn ($builder) => $builder->where('name', 'like', $like)->orWhere('summary', 'like', $like)->orWhere('region', 'like', $like))
            ->limit(20)->get(['name', 'slug', 'summary'])
            ->map(fn (Destination $destination): array => ['title' => $destination->name, 'summary' => (string) $destination->summary, 'url' => route('explore-bangladesh.show', $destination->slug), 'type' => __('Destination')]);

        $offers = Offer::query()->current()
            ->where(fn ($builder) => $builder->where('title', 'like', $like)->orWhere('summary', 'like', $like))
            ->limit(20)->get(['title', 'slug', 'summary'])
            ->map(fn (Offer $offer): array => ['title' => $offer->title, 'summary' => $offer->summary, 'url' => route('offers.index').'#'.$offer->slug, 'type' => __('Offer')]);

        $content = ContentEntry::query()->where('status', ContentEntry::STATUS_PUBLISHED)
            ->whereNotIn('type', [ContentType::Faq->value, ContentType::PageSection->value])
            ->where(fn ($builder) => $builder->where('title', 'like', $like)->orWhere('summary', 'like', $like)->orWhere('title_bn', 'like', $like))
            ->limit(30)->get()
            ->map(fn (ContentEntry $entry): array => ['title' => (string) $entry->localized('title'), 'summary' => (string) $entry->localized('summary'), 'url' => (string) $entry->publicUrl(), 'type' => __($entry->type->label())])
            ->filter(fn (array $result): bool => $result['url'] !== '');

        return $pages->concat($services)->concat($destinations)->concat($offers)->concat($content)->values();
    }
}
