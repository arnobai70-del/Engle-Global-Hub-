<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Services\Consultancy\StudyCountries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One piece of admin-managed website content (company profile, business
 * service, restaurant, article, success story, guide, FAQ or page section).
 * Text is plain text; blank lines start new paragraphs. No HTML is stored.
 */
class ContentEntry extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /** @var list<string> */
    public const STATUSES = [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED];

    public const REVIEW_APPROVED = 'approved';

    /** @var array<string, string> */
    public const REVIEW_STATUSES = [
        'needs_source_verification' => 'Needs source verification',
        'needs_owner_review' => 'Needs owner review',
        'needs_native_review' => 'Needs native Bengali review',
        'changes_requested' => 'Changes requested by owner',
        self::REVIEW_APPROVED => 'Approved for publishing',
    ];

    /** @var list<string> */
    protected $fillable = [
        'type', 'slug', 'title', 'summary', 'body', 'title_bn', 'summary_bn', 'body_bn',
        'category', 'location', 'country_id', 'intake', 'contact_phone', 'contact_email', 'website_url', 'latitude', 'longitude',
        'cover_media_id', 'source_urls', 'consent_reference', 'consent_recorded_at',
        'related_destination_ids', 'related_entry_ids',
        'seo_title', 'seo_description', 'canonical_url', 'sort_order',
        'review_status', 'last_verified_at', 'review_notes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'source_urls' => 'array',
            'related_destination_ids' => 'array',
            'related_entry_ids' => 'array',
            'consent_recorded_at' => 'datetime',
            'last_verified_at' => 'date',
            'owner_approved_at' => 'datetime',
            'published_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'sort_order' => 'integer',
        ];
    }

    /** Study country of a guide (Malaysia, Singapore or a study destination). */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function gallery(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'content_entry_media')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class)->latest('id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_approved_by_user_id');
    }

    /**
     * @param  Builder<ContentEntry>  $query
     */
    public function scopePublished(Builder $query, ContentType $type): void
    {
        $query->where('type', $type->value)->where('status', self::STATUS_PUBLISHED)->orderBy('sort_order')->orderBy('title');
    }

    /**
     * Localised field value: the Bengali text when the site is in Bengali and
     * a translation exists, otherwise English.
     */
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'bn' && in_array($field, ['title', 'summary', 'body'], true)) {
            $translated = $this->getAttribute($field.'_bn');
            if (is_string($translated) && trim($translated) !== '') {
                return $translated;
            }
        }

        $value = $this->getAttribute($field);

        return is_string($value) ? $value : null;
    }

    public function publicUrl(): ?string
    {
        if ($this->type === ContentType::Guide) {
            $study = app(StudyCountries::class)->forGuide($this);

            return $this->status === self::STATUS_PUBLISHED ? route($study['guide'], [...$study['params'], 'slug' => $this->slug]) : null;
        }
        $route = $this->type->publicRoute();

        return $route !== null && $this->status === self::STATUS_PUBLISHED ? route($route, $this->slug) : null;
    }

    /**
     * Reasons this entry cannot be published yet; empty when it may be.
     *
     * @return list<string>
     */
    public function publishBlockers(): array
    {
        $blockers = [];

        if ($this->review_status !== null && $this->review_status !== self::REVIEW_APPROVED) {
            $blockers[] = 'Review status is "'.(self::REVIEW_STATUSES[$this->review_status] ?? $this->review_status).'". An owner must mark it approved before publishing.';
        }
        if (trim((string) $this->summary) === '' && ! in_array($this->type, [ContentType::PageSection, ContentType::Faq], true)) {
            $blockers[] = 'Add a summary.';
        }
        if ($this->type->requiresOwnerApproval() && $this->owner_approved_at === null) {
            $blockers[] = 'An owner (super admin) must approve these claims before publishing.';
        }
        if ($this->type->requiresOfficialSource() && collect($this->source_urls ?? [])->filter(fn ($url) => is_string($url) && str_starts_with($url, 'https://'))->isEmpty()) {
            $blockers[] = 'Add at least one official https source for the information.';
        }
        if ($this->type->requiresConsent() && ($this->consent_recorded_at === null || blank($this->consent_reference))) {
            $blockers[] = "Record the student's consent (reference and date) before publishing.";
        }

        return $blockers;
    }
}
