<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\DestinationListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Test data only: fictional listings, never real hotels or restaurants.
 *
 * @extends Factory<DestinationListing>
 */
class DestinationListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'destination_id' => Destination::factory(),
            'type' => fake()->randomElement(array_keys(DestinationListing::TYPES)),
            'name' => 'Test '.fake()->unique()->words(2, true),
            'area' => null,
            'price_range' => null,
            'description' => null,
            'website_url' => null,
            'map_url' => null,
            'sort_order' => 0,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }
}
