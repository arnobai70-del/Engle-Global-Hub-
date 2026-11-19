<?php

namespace App\Http\Controllers;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Services\Consultancy\StudyCountries;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public pages for published admin-managed content. Drafts and archived
 * entries are never shown here.
 */
class ContentPageController extends Controller
{
    /** @var array<string, array{type: ContentType, title: string, intro: string, category?: string}> */
    private const LISTINGS = [
        'company' => ['type' => ContentType::CompanyProfile, 'title' => 'Company Profile', 'intro' => 'About Eagle Global Hub LTD, its business and its credentials.'],
        'business-services' => ['type' => ContentType::BusinessService, 'title' => 'Business Services', 'intro' => 'Export & import and civil & construction services from Eagle Global Hub LTD.'],
        'restaurants' => ['type' => ContentType::Restaurant, 'title' => 'Restaurants', 'intro' => 'Restaurants recommended by our travel team.'],
        'inspiration' => ['type' => ContentType::Inspiration, 'title' => 'Get Inspired for Your Next Trip', 'intro' => 'Travel ideas and destination guides from our team.'],
        'success-stories' => ['type' => ContentType::SuccessStory, 'title' => 'Success Stories', 'intro' => 'Stories shared by our students with their consent.'],
    ];

    public function index(string $listing): View
    {
        $config = self::LISTINGS[$listing] ?? abort(404);

        return view('content.index', [
            'heading' => __($config['title']),
            'intro' => __($config['intro']),
            'entries' => ContentEntry::query()->published($config['type'])->with('cover')->get(),
        ]);
    }

    public function show(Request $request): View
    {
        // Read by name: /study-in-{destination}/guides/{slug} has two route parameters.
        $slug = (string) $request->route('slug');
        $routeName = (string) $request->route()?->getName();
        $isGuide = in_array($routeName, ['consultancy.malaysia.guides.show', 'study-destinations.guides.show'], true);
        $type = $isGuide ? ContentType::Guide : $this->typeForRoute($routeName);
        $query = ContentEntry::query()->published($type)->where('slug', $slug)->with(['cover', 'gallery']);
        if ($type === ContentType::Guide) {
            $countries = app(StudyCountries::class);
            $countries->scopeGuides($query, $routeName === 'consultancy.malaysia.guides.show' ? 'MY' : $countries->forRoute($request)['code']);
        }
        $entry = $query->firstOrFail();

        return view('content.show', [
            'entry' => $entry,
            'preview' => false,
            'related' => ContentEntry::query()->whereIn('id', $entry->related_entry_ids ?? [])->where('status', ContentEntry::STATUS_PUBLISHED)->get(),
            'relatedDestinations' => Destination::query()->published()->whereIn('id', $entry->related_destination_ids ?? [])->get(['id', 'name', 'slug', 'summary']),
            'projects' => $type === ContentType::BusinessService && $entry->category !== null
                ? ContentEntry::query()->published(ContentType::Project)->where('category', $entry->category)->with('cover')->get()
                : collect(),
        ]);
    }

    private function typeForRoute(string $routeName): ContentType
    {
        foreach (ContentType::cases() as $type) {
            if ($type->publicRoute() === $routeName) {
                return $type;
            }
        }

        abort(404);
    }
}
