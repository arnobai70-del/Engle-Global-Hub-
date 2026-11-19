<?php

namespace App\Http\Controllers\Consultancy;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\ContentEntry;
use App\Services\Consultancy\StudyCountries;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public index of study guides for one destination (Malaysia or Singapore). Only published entries are listed;
 * drafts and entries still under review are never shown.
 */
class SingaporeGuideIndexController extends Controller
{
    /** Topic used when a guide has no category set in the CMS. */
    private const DEFAULT_TOPICS = [
        'students-pass-guidance' => 'students-pass',
        'pre-departure' => 'students-pass',
        'college-admission' => 'admission',
        'university-admission' => 'admission',
        'course-selection' => 'courses-costs',
        'tuition-fees' => 'courses-costs',
        'student-accommodation' => 'living',
    ];

    /** @var array<string, string> */
    private const TOPIC_LABELS = [
        'students-pass' => "Student's Pass & arrival",
        'admission' => 'Admission',
        'courses-costs' => 'Courses & costs',
        'living' => 'Living in Singapore',
        'general' => 'General',
    ];

    public function __invoke(Request $request, StudyCountries $countries): View
    {
        $study = $countries->forRoute($request);
        $guides = $countries->scopeGuides(ContentEntry::query()->published(ContentType::Guide), $study['code'])
            ->where(fn ($query) => $query->whereNull('review_status')->orWhere('review_status', ContentEntry::REVIEW_APPROVED))
            ->get()
            ->map(fn (ContentEntry $guide): array => ['entry' => $guide, 'topic' => $this->topicOf($guide)]);

        $topics = $guides->pluck('topic')->unique('key')->sortBy('label')->values();
        $search = trim((string) $request->query('q', ''));
        $topic = (string) $request->query('topic', '');

        $results = $guides
            ->when($topic !== '', fn (Collection $items) => $items->where('topic.key', $topic))
            ->when($search !== '', fn (Collection $items) => $items->filter(fn (array $item): bool => $this->matches($item['entry'], $search)))
            ->values();

        return view('consultancy.guides', [
            'results' => $results,
            'topics' => $topics,
            'filters' => ['q' => $search, 'topic' => $topic],
            'total' => $guides->count(),
            'study' => $study,
        ]);
    }

    /**
     * @return array{key: string, label: string}
     */
    private function topicOf(ContentEntry $guide): array
    {
        if (filled($guide->category)) {
            return ['key' => Str::slug((string) $guide->category), 'label' => (string) $guide->category];
        }

        $key = self::DEFAULT_TOPICS[$guide->slug] ?? 'general';

        return ['key' => $key, 'label' => __(self::TOPIC_LABELS[$key])];
    }

    private function matches(ContentEntry $guide, string $search): bool
    {
        $haystack = mb_strtolower(implode(' ', [$guide->title, $guide->summary, $guide->title_bn, $guide->summary_bn, $guide->body, $guide->body_bn]));

        return str_contains($haystack, mb_strtolower($search));
    }
}
