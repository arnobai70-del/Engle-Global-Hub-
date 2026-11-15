<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
    /** @var list<string> */
    protected $fillable = [
        'country_id', 'name', 'slug', 'region', 'summary', 'description',
        'cover_image_alt', 'meta_title', 'meta_description', 'sort_order', 'is_published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer', 'country_id' => 'integer'];
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
     * @param  Builder<Destination>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }
}
