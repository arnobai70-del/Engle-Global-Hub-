<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\StudyDestination;
use App\Models\StudyDestinationItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StudyDestination>
 */
class StudyDestinationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => fn (): int => Country::query()->create([
                'name' => $name = 'Testland '.Str::upper(Str::random(4)),
                'iso2' => Str::upper(Str::random(2)),
                'iso3' => Str::upper(Str::random(3)),
                'is_active' => true,
            ])->id,
            'slug' => fn (array $attributes): string => Str::slug((string) Country::query()->whereKey($attributes['country_id'])->value('name')),
            'status' => StudyDestination::STATUS_DRAFT,
        ];
    }

    public function forCountry(Country $country): static
    {
        return $this->state(['country_id' => $country->id, 'slug' => Str::slug($country->name)]);
    }

    /** Published with the minimum a public page needs: intro, a requirement, a visa step and a checked source. */
    public function publishedAndComplete(): static
    {
        return $this->state([
            'status' => StudyDestination::STATUS_PUBLISHED,
            'published_at' => now(),
            'intro' => 'Test introduction for this study destination.',
        ])->afterCreating(function (StudyDestination $destination): void {
            StudyDestinationItem::factory()->for($destination, 'destination')->create(['section' => 'requirement', 'title' => 'Academic', 'body' => 'Higher secondary certificate.', 'source_url' => 'https://example.gov/requirements', 'source_label' => 'Example Ministry']);
            StudyDestinationItem::factory()->for($destination, 'destination')->create(['section' => 'visa_step', 'title' => 'Apply online', 'body' => 'Submit the application.', 'source_url' => 'https://example.gov/visa', 'source_label' => 'Example Immigration']);
        });
    }
}
