<?php

namespace App\Models;

use Database\Factories\DestinationListingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A hotel, restaurant or experience at an Explore Bangladesh destination. Staff enter
 * only verified listings; nothing is shown publicly until it is published.
 */
class DestinationListing extends Model
{
    /** @use HasFactory<DestinationListingFactory> */
    use HasFactory;

    /** type => English label of the public block. */
    public const TYPES = [
        'hotel' => 'Where to stay',
        'restaurant' => 'Where to eat',
        'experience' => 'Things to do',
    ];

    /** price_range => English label. */
    public const PRICE_RANGES = [
        'budget' => 'Budget',
        'mid' => 'Mid-range',
        'premium' => 'Premium',
    ];

    /** @var list<string> */
    protected $fillable = [
        'type', 'name', 'area', 'price_range', 'description', 'website_url', 'map_url', 'sort_order', 'is_published',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['destination_id' => 'integer', 'sort_order' => 'integer', 'is_published' => 'boolean'];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function priceLabel(): ?string
    {
        return $this->price_range !== null && isset(self::PRICE_RANGES[$this->price_range]) ? __(self::PRICE_RANGES[$this->price_range]) : null;
    }
}
