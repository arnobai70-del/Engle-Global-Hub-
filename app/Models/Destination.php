<?php

namespace App\Models;

use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A travel destination (for example in Explore Bangladesh), shown publicly
 * only once published.
 */
class Destination extends Model
{
    /** @use HasFactory<DestinationFactory> */
    use HasFactory;

    /** Explore Bangladesh categories (filter chips and badges), key => English label. */
    public const CATEGORIES = [
        'historical' => 'Historical & heritage',
        'beach' => 'Beaches & islands',
        'hills' => 'Hills & tea gardens',
        'nature' => 'Forests & wildlife',
        'city' => 'City life',
        'religious' => 'Religious sites',
    ];

    /** @var list<string> */
    protected $fillable = [
        'country_id', 'name', 'name_bn', 'slug', 'region', 'category', 'summary', 'summary_bn', 'description', 'description_bn',
        'best_time_to_visit', 'how_to_get_there', 'travel_tips', 'latitude', 'longitude',
        'cover_image_alt', 'meta_title', 'meta_description', 'sort_order', 'is_published', 'is_featured',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'country_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Admin-chosen "Places You May Like" for this destination.
     */
    public function recommendations(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'destination_recommendations', 'destination_id', 'recommended_destination_id')
            ->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Hotels, restaurants and experiences entered by staff.
     *
     * @return HasMany<DestinationListing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(DestinationListing::class)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return HasMany<DestinationListing, $this>
     */
    public function publishedListings(): HasMany
    {
        return $this->listings()->where('is_published', true);
    }

    /**
     * @param  Builder<Destination>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Published Bangladesh destinations (the Explore Bangladesh section).
     *
     * @param  Builder<Destination>  $query
     */
    public function scopeInBangladesh(Builder $query): void
    {
        $query->whereHas('country', fn (Builder $country) => $country->where('iso2', 'BD'));
    }

    /**
     * Name, summary or description in the visitor's language; Bengali falls back to English.
     */
    public function localized(string $field): ?string
    {
        if (app()->getLocale() === 'bn' && in_array($field, ['name', 'summary', 'description'], true)) {
            $translated = $this->getAttribute($field.'_bn');
            if (is_string($translated) && trim($translated) !== '') {
                return $translated;
            }
        }

        $value = $this->getAttribute($field);

        return is_string($value) ? $value : null;
    }

    public function categoryLabel(): ?string
    {
        return $this->category !== null && isset(self::CATEGORIES[$this->category]) ? __(self::CATEGORIES[$this->category]) : null;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
