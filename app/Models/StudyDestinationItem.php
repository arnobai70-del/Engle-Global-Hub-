<?php

namespace App\Models;

use Database\Factories\StudyDestinationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row of a study destination section (a requirement, a visa step, a cost line,
 * a source …). Factual rows carry their official source.
 */
class StudyDestinationItem extends Model
{
    /** @use HasFactory<StudyDestinationItemFactory> */
    use HasFactory;

    protected $fillable = [
        'study_destination_id', 'section', 'title', 'title_bn', 'body', 'body_bn', 'amount',
        'source_url', 'source_label', 'needs_verification', 'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['needs_verification' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return BelongsTo<StudyDestination, $this> */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(StudyDestination::class, 'study_destination_id');
    }

    public function localized(string $field): ?string
    {
        $value = app()->getLocale() === 'bn' && filled($this->{$field.'_bn'} ?? null) ? $this->{$field.'_bn'} : $this->{$field};

        return filled($value) ? trim((string) $value) : null;
    }

    /** An https source the owner has checked. A seeded suggestion stays unverified until they do. */
    public function isVerifiedSource(): bool
    {
        return ! $this->needs_verification && $this->source_url !== null && str_starts_with($this->source_url, 'https://');
    }

    /** "Source: …" label for a factual row. */
    public function sourceLabel(): string
    {
        return filled($this->source_label) ? (string) $this->source_label : (string) parse_url((string) $this->source_url, PHP_URL_HOST);
    }
}
