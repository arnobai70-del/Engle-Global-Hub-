<?php

namespace App\Models;

use Database\Factories\StudyDestinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A study destination page at /study-in-{slug} (every study country except Malaysia and
 * Singapore, which have their own pages). Public only when published and complete.
 */
class StudyDestination extends Model
{
    /** @use HasFactory<StudyDestinationFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    /** Lower-case words joined by dashes; Malaysia and Singapore keep their own routes. */
    public const SLUG_PATTERN = '(?!(?:malaysia|singapore)(?![a-z-]))[a-z]+(?:-[a-z]+)*';

    /**
     * List sections: key => [English heading, has a source (factual), fields shown in admin].
     *
     * @var array<string, array{0: string, 1: bool, 2: list<string>}>
     */
    public const SECTIONS = [
        'why' => ['Why study there', false, ['title', 'body']],
        'subject' => ['Popular subjects and levels', false, ['title', 'body']],
        'requirement' => ['Entry requirements', true, ['title', 'body']],
        'document' => ['Documents checklist', false, ['title']],
        'visa_step' => ['Student visa steps', true, ['title', 'body']],
        'cost' => ['Costs (local currency)', true, ['title', 'amount', 'body']],
        'work_rule' => ['Work while studying', true, ['title', 'body']],
        'faq' => ['FAQ', false, ['title', 'body']],
        'source' => ['Official sources', false, []],
    ];

    protected $fillable = [
        'country_id', 'slug', 'name_bn', 'name_in_sentence', 'name_bn_locative', 'status', 'published_at',
        'hero_title', 'hero_title_bn', 'intro', 'intro_bn', 'hero_media_id', 'hero_alt', 'hero_alt_bn',
        'visa_name', 'visa_name_bn', 'visa_authority', 'visa_authority_bn',
        'processing_time', 'processing_time_bn', 'processing_source_url', 'processing_source_label',
        'currency_code', 'bdt_rate', 'bdt_rate_checked_on', 'intakes', 'intakes_bn', 'sources_seeded_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'bdt_rate' => 'decimal:4',
            'bdt_rate_checked_on' => 'date',
            'sources_seeded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Country, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** @return BelongsTo<MediaAsset, $this> */
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'hero_media_id');
    }

    /** @return HasMany<StudyDestinationItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(StudyDestinationItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @param Builder<StudyDestination> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Rows of one section.
     *
     * @return Collection<int, StudyDestinationItem>
     */
    public function section(string $section): Collection
    {
        return $this->items->where('section', $section)->values();
    }

    /** The country name in the visitor's language. */
    public function name(): string
    {
        $name = (string) $this->country?->name;

        return app()->getLocale() === 'bn' && filled($this->name_bn) ? (string) $this->name_bn : __($name);
    }

    /**
     * The country inside a sentence: "Why study in the United Kingdom". English uses the optional
     * name_in_sentence (for countries that need "the"), otherwise the plain name; Bengali uses name().
     */
    public function sentenceName(): string
    {
        if (app()->getLocale() !== 'bn' && filled($this->name_in_sentence)) {
            return trim((string) $this->name_in_sentence);
        }

        return $this->name();
    }

    /**
     * Replacements for the country sentences on the study pages:
     * - :country    the name as it reads in a sentence ("the United Kingdom"; Bengali: the plain name);
     * - :in_country the Bengali place form ("জাপানে"). Without one, "জাপান:", so Bengali
     *               templates start with it (":in_country পড়াশোনার পরিকল্পনা করুন") and never glue
     *               a suffix onto a name. English templates use only :country.
     *
     * @return array{country: string, in_country: string}
     */
    public function sentenceReplacements(): array
    {
        $locative = trim((string) $this->name_bn_locative);

        return ['country' => $this->sentenceName(), 'in_country' => $locative !== '' ? $locative : $this->name().':'];
    }

    /** A field in the visitor's language, falling back to English. */
    public function localized(string $field): ?string
    {
        $value = app()->getLocale() === 'bn' && filled($this->{$field.'_bn'} ?? null) ? $this->{$field.'_bn'} : $this->{$field};

        return filled($value) ? trim((string) $value) : null;
    }

    /** Has at least one official source the owner has checked (seeded suggestions do not count). */
    public function hasVerifiedSource(): bool
    {
        return $this->items->contains(fn (StudyDestinationItem $item): bool => $item->isVerifiedSource())
            || ($this->processing_source_url !== null && str_starts_with($this->processing_source_url, 'https://') && filled($this->processing_time));
    }

    /**
     * What is still missing before the page may be public; empty when it is complete.
     *
     * @return list<string>
     */
    public function publishBlockers(): array
    {
        return array_values(array_filter([
            filled($this->intro) ? null : 'Write the introduction (English).',
            $this->section('requirement')->contains(fn (StudyDestinationItem $item): bool => filled($item->title)) ? null : 'Add at least one entry requirement.',
            $this->section('visa_step')->contains(fn (StudyDestinationItem $item): bool => filled($item->title)) ? null : 'Add at least one student visa step.',
            $this->hasVerifiedSource() ? null : 'Add at least one official source you have checked (https link, "verify before publishing" switched off).',
            $this->hero_media_id !== null && blank($this->hero_alt) ? 'Write the alt text (English) for the hero image.' : null,
        ]));
    }

    public function isComplete(): bool
    {
        return $this->publishBlockers() === [];
    }

    public function isPublic(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->isComplete();
    }

    /**
     * Factual rows that have content but no source, for the admin warning.
     *
     * @return Collection<int, StudyDestinationItem>
     */
    public function factsWithoutSource(): Collection
    {
        return $this->items->filter(fn (StudyDestinationItem $item): bool => (self::SECTIONS[$item->section][1] ?? false) && filled($item->title) && blank($item->source_url))->values();
    }
}
