<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Explore Bangladesh destinations named in the requirements document. Seeded
 * as unpublished drafts with names only; staff add verified content and
 * photos before publishing. Existing records are never overwritten.
 */
class BangladeshDestinationSeeder extends Seeder
{
    /** @var array<string, string> name => slug */
    private const DESTINATIONS = [
        "Cox's Bazar" => 'coxs-bazar',
        'Sylhet' => 'sylhet',
        'Kuakata' => 'kuakata',
    ];

    public function run(): void
    {
        $bangladeshId = Country::query()->where('iso2', 'BD')->value('id');
        if ($bangladeshId === null) {
            return;
        }

        $position = 0;
        foreach (self::DESTINATIONS as $name => $slug) {
            Destination::query()->firstOrCreate(
                ['slug' => $slug],
                ['country_id' => $bangladeshId, 'name' => $name, 'sort_order' => ++$position, 'is_published' => false],
            );
        }
    }
}
