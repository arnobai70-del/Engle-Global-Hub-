<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Test data only: fictional destinations in Bangladesh.
 *
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Test Place '.Str::title(fake()->unique()->word()).' '.fake()->unique()->numberBetween(1, 99999);

        return [
            'country_id' => fn (): int => (int) (Country::query()->where('iso2', 'BD')->value('id')
                ?? Country::query()->create(['name' => 'Bangladesh', 'iso2' => 'BD', 'iso3' => 'BGD', 'is_active' => true])->id),
            'name' => $name,
            'slug' => Str::slug($name),
            'region' => 'Dhaka',
            'category' => null,
            'summary' => 'A test summary.',
            'sort_order' => 0,
            'is_published' => false,
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true]);
    }

    public function category(string $category): static
    {
        return $this->state(['category' => $category]);
    }
}
