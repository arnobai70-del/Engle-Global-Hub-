<?php

namespace App\Services\Consultancy;

use App\Models\ContentEntry;
use Illuminate\Database\Eloquent\Builder;

/**
 * The study destinations that have their own section (landing page, guides,
 * institutions). Malaysia is the main destination; Singapore is second.
 *
 * Guides belong to Malaysia when their "Location" in Admin › Website Content is
 * "Malaysia"; every other guide belongs to Singapore. Institutions follow their country.
 */
class StudyCountries
{
    /** @var array<string, array<string, string>> */
    private const COUNTRIES = [
        'MY' => [
            'code' => 'MY', 'name' => 'Malaysia', 'location' => 'Malaysia',
            'page' => 'consultancy.malaysia', 'guides' => 'consultancy.malaysia.guides.index',
            'guide' => 'consultancy.malaysia.guides.show', 'institutions' => 'consultancy.malaysia.institutions.index',
            'authority' => 'EMGS', 'pass' => 'Student Pass',
        ],
        'SG' => [
            'code' => 'SG', 'name' => 'Singapore', 'location' => 'Singapore',
            'page' => 'consultancy.singapore', 'guides' => 'consultancy.guides.index',
            'guide' => 'consultancy.guides.show', 'institutions' => 'consultancy.institutions.index',
            'authority' => 'ICA', 'pass' => 'Student\'s Pass',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public function get(string $code): array
    {
        return self::COUNTRIES[strtoupper($code)] ?? self::COUNTRIES['SG'];
    }

    /**
     * Limits a guide query to one destination.
     *
     * @param  Builder<ContentEntry>  $query
     * @return Builder<ContentEntry>
     */
    public function scopeGuides(Builder $query, string $code): Builder
    {
        return strtoupper($code) === 'MY'
            ? $query->where('location', 'Malaysia')
            : $query->where(fn (Builder $inner) => $inner->whereNull('location')->orWhere('location', '!=', 'Malaysia'));
    }

    public function forGuide(ContentEntry $guide): array
    {
        return $this->get($guide->location === 'Malaysia' ? 'MY' : 'SG');
    }
}
