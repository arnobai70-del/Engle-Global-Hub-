<?php

namespace Database\Seeders;

use App\Enums\BusinessService;
use App\Models\Country;
use App\Models\ServiceDestination;
use Illuminate\Database\Seeder;

/**
 * Destination countries from the business requirements document. Existing
 * rows are never overwritten, so staff changes survive re-seeding.
 */
class ServiceDestinationSeeder extends Seeder
{
    /** @var array<string, list<string>> service => ISO2 codes in display order */
    private const DESTINATIONS = [
        'tourist_visa' => ['IN', 'LK', 'TH', 'MY', 'SG', 'CN', 'PH', 'EG', 'AE', 'VN', 'KR', 'PK', 'ID', 'KH', 'CA', 'DE', 'SE', 'GB', 'US', 'FR', 'AT'],
        // Singapore leads as the primary study destination.
        'student_visa' => ['SG', 'MY', 'BG', 'GB', 'JP', 'KR', 'CY'],
        'work_visa' => ['AE', 'MY', 'JP', 'JO', 'SA', 'CA', 'BY'],
    ];

    public function run(): void
    {
        $countryIds = Country::query()->pluck('id', 'iso2');

        foreach (self::DESTINATIONS as $service => $codes) {
            foreach ($codes as $position => $code) {
                if (! isset($countryIds[$code])) {
                    continue;
                }

                ServiceDestination::query()->firstOrCreate(
                    ['service' => BusinessService::from($service), 'country_id' => $countryIds[$code]],
                    ['is_active' => true, 'sort_order' => $position + 1],
                );
            }
        }
    }
}
