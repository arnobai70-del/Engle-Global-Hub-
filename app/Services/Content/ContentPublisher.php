<?php

namespace App\Services\Content;

use App\Models\ContentEntry;
use App\Models\User;
use App\Services\ActivityRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves, publishes, archives and approves content entries, keeping a
 * revision snapshot of every save.
 */
class ContentPublisher
{
    /** Fields copied into revision snapshots and restorable from them. */
    private const SNAPSHOT_FIELDS = [
        'slug', 'title', 'summary', 'body', 'title_bn', 'summary_bn', 'body_bn', 'category', 'location',
        'contact_phone', 'contact_email', 'website_url', 'latitude', 'longitude', 'cover_media_id', 'source_urls',
        'consent_reference', 'consent_recorded_at', 'related_destination_ids', 'related_entry_ids',
        'seo_title', 'seo_description', 'canonical_url', 'sort_order',
        'review_status', 'last_verified_at', 'review_notes',
    ];

    public function __construct(private ActivityRecorder $activity) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>|null  $galleryMediaIds
     */
    public function save(ContentEntry $entry, array $attributes, ?array $galleryMediaIds, User $actor): ContentEntry
    {
        return DB::transaction(function () use ($entry, $attributes, $galleryMediaIds, $actor): ContentEntry {
            $creating = ! $entry->exists;
            $claimsChanged = $entry->exists && $entry->type->requiresOwnerApproval()
                && collect(['title', 'summary', 'body', 'title_bn', 'summary_bn', 'body_bn'])->contains(fn (string $field): bool => array_key_exists($field, $attributes) && $attributes[$field] !== $entry->getAttribute($field));

            $entry->fill($attributes);
            if ($creating) {
                $entry->status = ContentEntry::STATUS_DRAFT;
                $entry->created_by_user_id = $actor->getKey();
            }
            if ($claimsChanged) {
                // Edited claims need fresh approval; published claims go back to draft.
                $entry->owner_approved_at = null;
                $entry->owner_approved_by_user_id = null;
                if ($entry->status === ContentEntry::STATUS_PUBLISHED) {
                    $entry->status = ContentEntry::STATUS_DRAFT;
                }
            }
            $entry->updated_by_user_id = $actor->getKey();
            $entry->save();

            if ($galleryMediaIds !== null) {
                $entry->gallery()->sync(collect($galleryMediaIds)->values()->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])->all());
            }

            $this->snapshot($entry, $actor);
            $this->activity->record($actor, $creating ? 'content.created' : 'content.updated', $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);

            return $entry;
        });
    }

    /**
     * @throws ValidationException when the type's publishing rules are not met
     */
    public function publish(ContentEntry $entry, User $actor): void
    {
        $blockers = $entry->publishBlockers();
        if ($blockers !== []) {
            throw ValidationException::withMessages(['status' => $blockers]);
        }

        $entry->forceFill(['status' => ContentEntry::STATUS_PUBLISHED, 'published_at' => $entry->published_at ?? now(), 'updated_by_user_id' => $actor->getKey()])->save();
        $this->snapshot($entry, $actor);
        $this->activity->record($actor, 'content.published', $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);
    }

    public function setStatus(ContentEntry $entry, string $status, User $actor): void
    {
        $entry->forceFill(['status' => $status, 'updated_by_user_id' => $actor->getKey()])->save();
        $this->snapshot($entry, $actor);
        $this->activity->record($actor, 'content.'.$status, $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);
    }

    public function approve(ContentEntry $entry, User $actor): void
    {
        $entry->forceFill(['owner_approved_at' => now(), 'owner_approved_by_user_id' => $actor->getKey()])->save();
        $this->activity->record($actor, 'content.approved', $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);
    }

    /**
     * Owner sign-off from the approval dashboard. Marks the entry approved for
     * publishing (and approves claims where required) but never publishes it.
     *
     * @throws ValidationException when the official sources are still unconfirmed
     */
    public function approveForPublishing(ContentEntry $entry, User $actor): void
    {
        if ($entry->review_status === 'needs_source_verification') {
            throw ValidationException::withMessages(['review_status' => 'Confirm the official sources first, then set the review status to "Needs owner review".']);
        }

        DB::transaction(function () use ($entry, $actor): void {
            $this->snapshot($entry, $actor);
            $entry->forceFill(['review_status' => ContentEntry::REVIEW_APPROVED])->save();
            if ($entry->type->requiresOwnerApproval() && $entry->owner_approved_at === null) {
                $this->approve($entry, $actor);
            }
            $this->activity->record($actor, 'content.review-approved', $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);
        });
    }

    /**
     * Sends an entry back for changes, keeping the reason in the internal review notes.
     */
    public function requestChanges(ContentEntry $entry, string $reason, User $actor): void
    {
        DB::transaction(function () use ($entry, $reason, $actor): void {
            $this->snapshot($entry, $actor);
            $note = '['.now()->format('Y-m-d').' '.$actor->name.'] Changes requested: '.$reason;
            $entry->forceFill([
                'review_status' => 'changes_requested',
                'review_notes' => trim($note."\n\n".$entry->review_notes),
            ])->save();
            $this->activity->record($actor, 'content.changes-requested', $entry, ['type' => $entry->type->value, 'slug' => $entry->slug]);
        });
    }

    /**
     * Restores the text and settings from a revision as a new draft save.
     */
    public function restore(ContentEntry $entry, int $revisionId, User $actor): void
    {
        $revision = $entry->revisions()->whereKey($revisionId)->firstOrFail();
        $attributes = array_intersect_key((array) $revision->snapshot, array_flip(self::SNAPSHOT_FIELDS));
        $this->save($entry, $attributes, null, $actor);
        if ($entry->status === ContentEntry::STATUS_PUBLISHED && $entry->publishBlockers() !== []) {
            $this->setStatus($entry, ContentEntry::STATUS_DRAFT, $actor);
        }
    }

    private function snapshot(ContentEntry $entry, User $actor): void
    {
        $entry->revisions()->create([
            'user_id' => $actor->getKey(),
            'status' => $entry->status,
            'snapshot' => array_intersect_key($entry->attributesToArray(), array_flip(self::SNAPSHOT_FIELDS)),
        ]);
    }
}
