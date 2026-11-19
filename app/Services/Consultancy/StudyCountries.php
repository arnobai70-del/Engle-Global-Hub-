<?php

namespace App\Services\Consultancy;

use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Institution;
use App\Models\StudyDestination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The study destinations that have their own section (landing page, guides,
 * institutions). Malaysia is the main destination; Singapore is second; every other
 * study country gets a /study-in-{slug} page once it is published and complete
 * (Admin › Study destinations).
 *
 * A guide belongs to a destination through its study country (country_id). Guides without
 * one fall back to their "Location" text: "Malaysia" or a public destination's name in
 * English or Bengali. Everything else stays with Singapore, so publishing a destination
 * only moves that destination's own guides, institutions and courses.
 */
class StudyCountries
{
    /** @var array<string, array<string, mixed>> */
    private const COUNTRIES = [
        'MY' => [
            'code' => 'MY', 'name' => 'Malaysia', 'location' => 'Malaysia',
            'page' => 'consultancy.malaysia', 'guides' => 'consultancy.malaysia.guides.index',
            'guide' => 'consultancy.malaysia.guides.show', 'institutions' => 'consultancy.malaysia.institutions.index',
            'authority' => 'EMGS', 'pass' => 'Student Pass', 'params' => [],
        ],
        'SG' => [
            'code' => 'SG', 'name' => 'Singapore', 'location' => 'Singapore',
            'page' => 'consultancy.singapore', 'guides' => 'consultancy.guides.index',
            'guide' => 'consultancy.guides.show', 'institutions' => 'consultancy.institutions.index',
            'authority' => 'ICA', 'pass' => 'Student\'s Pass', 'params' => [],
        ],
    ];

    /** @var Collection<string, StudyDestination>|null public destinations keyed by ISO code */
    private ?Collection $public = null;

    /** @var array<string, int|null> */
    private array $countryIds = [];

    /**
     * @return array<string, mixed>
     */
    public function get(string $code): array
    {
        $code = strtoupper($code);
        if (isset(self::COUNTRIES[$code])) {
            return self::COUNTRIES[$code];
        }
        $destination = $this->publicDestinations()->get($code);

        return $destination !== null ? $this->forDestination($destination) : self::COUNTRIES['SG'];
    }

    /**
     * The destination of a guides or institutions route: the {destination} slug of
     * /study-in-{slug}/…, or the route's "country" default (MY or SG).
     *
     * @return array<string, mixed>
     */
    public function forRoute(Request $request): array
    {
        $slug = $request->route('destination');
        if (is_string($slug)) {
            $destination = $this->publicDestinations()->first(fn (StudyDestination $destination): bool => $destination->slug === $slug);
            abort_if($destination === null, 404);

            return $this->forDestination($destination);
        }

        return $this->get((string) ($request->route('country') ?? 'SG'));
    }

    /**
     * @return array<string, mixed>
     */
    public function forDestination(StudyDestination $destination): array
    {
        return [
            'code' => (string) $destination->country->iso2, 'name' => $destination->name(), 'location' => (string) $destination->country->name,
            'page' => 'study-destinations.show', 'guides' => 'study-destinations.guides.index',
            'guide' => 'study-destinations.guides.show', 'institutions' => 'study-destinations.institutions.index',
            'authority' => (string) $destination->localized('visa_authority'), 'pass' => (string) ($destination->localized('visa_name') ?? __('Student visa')),
            'params' => ['destination' => $destination->slug], 'country_id' => $destination->country_id,
        ];
    }

    /**
     * Published and complete destinations, keyed by ISO code (remembered for this instance).
     *
     * @return Collection<string, StudyDestination>
     */
    public function publicDestinations(): Collection
    {
        if ($this->public === null) {
            try {
                $this->public = StudyDestination::query()->published()->with(['country', 'items'])->get()
                    ->filter(fn (StudyDestination $destination): bool => $destination->country !== null && $destination->isPublic())
                    ->keyBy(fn (StudyDestination $destination): string => (string) $destination->country->iso2);
            } catch (QueryException) {
                $this->public = collect();
            }
        }

        return $this->public;
    }

    public function isPublic(string $code): bool
    {
        return $this->publicDestinations()->has(strtoupper($code));
    }

    /**
     * Limits a guide query to one destination.
     *
     * @param  Builder<ContentEntry>  $query
     * @return Builder<ContentEntry>
     */
    public function scopeGuides(Builder $query, string $code): Builder
    {
        $code = strtoupper($code);
        if ($code !== 'SG') {
            [$ids, $names] = $this->guideKeys([$code]);

            return $query->where(fn (Builder $inner) => $inner->whereIn('country_id', $ids)
                ->orWhere(fn (Builder $fallback) => $fallback->whereNull('country_id')->whereIn('location', $names)));
        }

        // Singapore keeps every guide that does not belong to Malaysia or a public destination.
        [$ids, $names] = $this->guideKeys(['MY', ...$this->publicDestinations()->keys()->all()]);

        return $query->where(fn (Builder $inner) => $inner->whereNull('country_id')->orWhereNotIn('country_id', $ids))
            ->where(fn (Builder $inner) => $inner->whereNotNull('country_id')->orWhereNull('location')->orWhereNotIn('location', $names));
    }

    /**
     * Limits an institution query to one destination (by the institution's country).
     *
     * @param  Builder<Institution>  $query
     * @return Builder<Institution>
     */
    public function scopeInstitutions(Builder $query, string $code): Builder
    {
        $code = strtoupper($code);
        if ($code !== 'SG') {
            return $query->whereHas('country', fn (Builder $country) => $country->where('iso2', $code));
        }

        $others = ['MY', ...$this->publicDestinations()->keys()->all()];

        return $query->where(fn (Builder $inner) => $inner->whereNull('country_id')
            ->orWhereHas('country', fn (Builder $country) => $country->whereNotIn('iso2', $others)));
    }

    /**
     * @return array<string, mixed>
     */
    public function forGuide(ContentEntry $guide): array
    {
        if ($guide->country_id !== null) {
            $code = (string) ($guide->relationLoaded('country') ? $guide->country?->iso2 : Country::query()->whereKey($guide->country_id)->value('iso2'));
            if ($code === 'MY' || $this->isPublic($code)) {
                return $this->get($code);
            }

            return self::COUNTRIES['SG'];
        }

        $location = trim((string) $guide->location);
        if ($location === 'Malaysia') {
            return self::COUNTRIES['MY'];
        }
        foreach ($this->publicDestinations() as $destination) {
            if (in_array($location, array_filter([$destination->country->name, $destination->name_bn]), true)) {
                return $this->forDestination($destination);
            }
        }

        return self::COUNTRIES['SG'];
    }

    /**
     * Country ids and Location names that mark a guide as belonging to the given destinations.
     *
     * @param  list<string>  $codes
     * @return array{0: list<int>, 1: list<string>}
     */
    private function guideKeys(array $codes): array
    {
        $ids = [];
        $names = [];
        foreach ($codes as $code) {
            if ($code === 'MY') {
                $names[] = 'Malaysia';
            } elseif (($destination = $this->publicDestinations()->get($code)) !== null) {
                $names = [...$names, ...array_filter([(string) $destination->country->name, (string) $destination->name_bn])];
            }
            if (($id = $this->countryId($code)) !== null) {
                $ids[] = $id;
            }
        }

        return [$ids, array_values(array_unique($names))];
    }

    private function countryId(string $code): ?int
    {
        if (! array_key_exists($code, $this->countryIds)) {
            try {
                $id = Country::query()->where('iso2', $code)->value('id');
            } catch (QueryException) {
                $id = null;
            }
            $this->countryIds[$code] = $id === null ? null : (int) $id;
        }

        return $this->countryIds[$code];
    }
}
