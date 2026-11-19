<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Consultancy\StoreStudentEnquiryRequest;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Destination;
use App\Models\MediaAsset;
use App\Services\Content\ContentPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentEntryController extends Controller
{
    public function __construct(private ContentPublisher $publisher) {}

    public function index(Request $request): View
    {
        $type = ContentType::tryFrom((string) $request->query('type', ''));
        $status = in_array($request->query('status'), ContentEntry::STATUSES, true) ? (string) $request->query('status') : '';
        $search = trim((string) $request->query('search', ''));

        return view('admin.content.index', [
            'entries' => ContentEntry::query()
                ->when($type, fn ($query) => $query->where('type', $type->value))
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$search.'%')->orWhere('slug', 'like', '%'.$search.'%')))
                ->orderBy('type')->orderBy('sort_order')->orderBy('title')->paginate(30)->withQueryString(),
            'filters' => ['type' => $type?->value ?? '', 'status' => $status, 'search' => $search],
            'types' => ContentType::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $type = ContentType::tryFrom((string) $request->query('type')) ?? abort(404);

        return view('admin.content.form', ['entry' => new ContentEntry(['type' => $type, 'sort_order' => 0]), ...$this->options()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = ContentType::tryFrom((string) $request->input('type')) ?? abort(422);
        $entry = new ContentEntry;
        $entry->type = $type;
        [$attributes, $gallery] = $this->validated($request, $entry);
        $this->publisher->save($entry, $attributes, $gallery, $request->user());

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Saved as a draft.');
    }

    public function edit(ContentEntry $entry): View
    {
        return view('admin.content.form', ['entry' => $entry->load(['gallery', 'revisions.user:id,name', 'approvedBy:id,name']), ...$this->options()]);
    }

    public function update(Request $request, ContentEntry $entry): RedirectResponse
    {
        [$attributes, $gallery] = $this->validated($request, $entry);
        $this->publisher->save($entry, $attributes, $gallery, $request->user());

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Saved.');
    }

    public function status(Request $request, ContentEntry $entry): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in(ContentEntry::STATUSES)]])['status'];

        $status === ContentEntry::STATUS_PUBLISHED
            ? $this->publisher->publish($entry, $request->user())
            : $this->publisher->setStatus($entry, $status, $request->user());

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Status changed to '.$status.'.');
    }

    public function approve(Request $request, ContentEntry $entry): RedirectResponse
    {
        abort_unless($entry->type->requiresOwnerApproval(), 404);
        $this->publisher->approve($entry, $request->user());

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Claims approved. The entry can now be published.');
    }

    public function restore(Request $request, ContentEntry $entry): RedirectResponse
    {
        $this->publisher->restore($entry, $request->integer('revision'), $request->user());

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Revision restored.');
    }

    /**
     * Shows a draft through the public template, for staff only.
     */
    public function preview(ContentEntry $entry): View
    {
        return view('content.show', ['entry' => $entry->load('gallery', 'cover'), 'preview' => true, 'related' => collect(), 'relatedDestinations' => collect()]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: list<int>}
     */
    private function validated(Request $request, ContentEntry $entry): array
    {
        $type = $entry->type;
        $request->merge([
            // An existing entry keeps its URL unless a new slug is entered explicitly.
            'slug' => Str::slug((string) ($request->input('slug') ?: ($entry->exists ? $entry->slug : $request->input('title')))),
            'source_urls' => collect(preg_split('/\R/', (string) $request->input('source_urls_text', '')))->map(fn ($line) => trim($line))->filter()->values()->all(),
        ]);

        $slugRule = $type === ContentType::PageSection
            ? Rule::in(array_keys(ContentType::pageSections()))
            : 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:160', $slugRule, Rule::unique('content_entries', 'slug')->where('type', $type->value)->ignore($entry)],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:30000'],
            'title_bn' => ['nullable', 'string', 'max:200'],
            'summary_bn' => ['nullable', 'string', 'max:500'],
            'body_bn' => ['nullable', 'string', 'max:30000'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:200'],
            // A guide's study country (Malaysia, Singapore or a study destination); empty = Singapore.
            'country_id' => ['nullable', 'integer', Rule::exists(Country::class, 'id')],
            'intake' => ['nullable', 'string', 'max:40'],
            'contact_phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{7,32}$/'],
            'contact_email' => ['nullable', 'email', 'max:254'],
            'website_url' => ['nullable', 'url:https', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'cover_media_id' => ['nullable', 'integer', Rule::exists(MediaAsset::class, 'id')],
            'gallery_media_ids' => ['nullable', 'array', 'max:24'],
            'gallery_media_ids.*' => ['integer', Rule::exists(MediaAsset::class, 'id')],
            'source_urls' => ['array', 'max:10'],
            'source_urls.*' => ['url:https', 'max:2048'],
            'consent_reference' => ['nullable', 'string', 'max:200'],
            'consent_recorded_at' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'related_destination_ids' => ['nullable', 'array', 'max:12'],
            'related_destination_ids.*' => ['integer', Rule::exists(Destination::class, 'id')],
            'related_entry_ids' => ['nullable', 'array', 'max:12'],
            'related_entry_ids.*' => ['integer', Rule::exists(ContentEntry::class, 'id'), Rule::notIn([$entry->getKey()])],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:170'],
            'canonical_url' => ['nullable', 'url:https', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'review_status' => ['nullable', Rule::in(array_keys(ContentEntry::REVIEW_STATUSES))],
            'last_verified_at' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        // Only the owner role may approve content for publishing.
        if (($data['review_status'] ?? null) === ContentEntry::REVIEW_APPROVED && $entry->review_status !== ContentEntry::REVIEW_APPROVED && ! $request->user()->can('content.approve')) {
            throw ValidationException::withMessages(['review_status' => 'Only an owner can mark content as approved.']);
        }

        foreach (['title', 'summary', 'body', 'title_bn', 'summary_bn', 'body_bn', 'category', 'location', 'intake', 'consent_reference', 'seo_title', 'seo_description', 'review_notes'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim(strip_tags($data[$field]));
            }
        }
        $gallery = array_map('intval', $data['gallery_media_ids'] ?? []);
        unset($data['gallery_media_ids']);
        $data['related_destination_ids'] = array_map('intval', $data['related_destination_ids'] ?? []);
        $data['related_entry_ids'] = array_map('intval', $data['related_entry_ids'] ?? []);

        return [$data, $gallery];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'media' => MediaAsset::query()->orderBy('collection')->latest('id')->limit(200)->get(['id', 'path', 'alt_text', 'collection']),
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'otherEntries' => ContentEntry::query()->whereNotIn('type', [ContentType::Faq->value, ContentType::PageSection->value])->orderBy('title')->get(['id', 'title', 'type']),
            'pageSections' => ContentType::pageSections(),
            'studyCountries' => rescue(fn () => StoreStudentEnquiryRequest::destinations(), collect(), false),
        ];
    }
}
