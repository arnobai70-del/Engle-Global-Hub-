<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactDirectoryEntry;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\ForumCategory;
use App\Models\RewardProgram;
use App\Models\TeamMember;
use App\Services\Content\ContentPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Owner-only review queue: every unpublished content entry with its sources,
 * review state and blockers. Approving never publishes anything.
 */
class ContentApprovalController extends Controller
{
    public function __construct(private ContentPublisher $publisher) {}

    public function index(Request $request): View
    {
        $reviewStatus = array_key_exists((string) $request->query('review'), ContentEntry::REVIEW_STATUSES) ? (string) $request->query('review') : '';

        $pending = ContentEntry::query()
            ->where('status', '!=', ContentEntry::STATUS_PUBLISHED)
            ->when($reviewStatus !== '', fn ($query) => $query->where('review_status', $reviewStatus))
            ->orderByRaw("case review_status when 'needs_owner_review' then 0 when 'changes_requested' then 1 when 'needs_native_review' then 2 when 'needs_source_verification' then 3 when 'approved' then 5 else 4 end")
            ->orderBy('type')->orderBy('title')
            ->get();

        return view('admin.content.approvals', [
            'entries' => $pending,
            'reviewStatus' => $reviewStatus,
            'counts' => ContentEntry::query()->where('status', '!=', ContentEntry::STATUS_PUBLISHED)
                ->selectRaw("coalesce(review_status, 'none') as review, count(*) as total")->groupBy('review')->pluck('total', 'review'),
            'otherPending' => $this->otherPending(),
        ]);
    }

    public function approve(Request $request, ContentEntry $entry): RedirectResponse
    {
        $request->validate([
            'confirm_reviewed' => ['accepted'],
            'confirm_sources' => $entry->type->requiresOfficialSource() ? ['required', 'accepted'] : ['prohibited'],
        ], [
            'confirm_reviewed.accepted' => 'Tick the box to confirm you have read the English and Bengali text.',
            'confirm_sources.required' => 'Tick the box to confirm you have checked the official sources.',
        ]);

        $this->publisher->approveForPublishing($entry, $request->user());

        return redirect()->route('admin.content.approvals', $request->only('review'))
            ->with('status', '"'.$entry->title.'" is approved. It is still a draft: publish it from the editor when you are ready.');
    }

    public function reject(Request $request, ContentEntry $entry): RedirectResponse
    {
        $reason = trim(strip_tags((string) $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']])['reason']));

        $this->publisher->requestChanges($entry, $reason, $request->user());

        return redirect()->route('admin.content.approvals', $request->only('review'))
            ->with('status', 'Changes requested for "'.$entry->title.'". It stays unpublished.');
    }

    /**
     * Items outside the content module that also wait for an owner decision.
     *
     * @return list<array{label: string, count: int, note: string, route: string}>
     */
    private function otherPending(): array
    {
        return [
            ['label' => 'Team members (unpublished)', 'count' => TeamMember::query()->where('is_published', false)->count(), 'note' => 'Publish only after each person has confirmed their name, role and photo.', 'route' => 'admin.team-members.index'],
            ['label' => 'Destinations (unpublished)', 'count' => Destination::query()->where('is_published', false)->count(), 'note' => 'Check descriptions and photos before publishing.', 'route' => 'admin.destinations.index'],
            ['label' => 'Rewards programmes (unpublished)', 'count' => RewardProgram::query()->where('is_published', false)->count(), 'note' => 'Needs owner-approved terms and earning rules.', 'route' => 'admin.rewards.index'],
            ['label' => 'Forum categories (closed)', 'count' => ForumCategory::query()->where('is_active', false)->count(), 'note' => 'Open only when a moderator is assigned.', 'route' => 'admin.forums.index'],
            ['label' => 'Public contact numbers', 'count' => ContactDirectoryEntry::query()->where('is_active', true)->where('is_public', true)->count(), 'note' => '0 means no official number is shown on the website yet.', 'route' => 'admin.contact-directory.index'],
        ];
    }
}
