<?php

namespace Database\Factories;

use App\Models\StudyDestination;
use App\Models\StudyDestinationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyDestinationItem>
 */
class StudyDestinationItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_destination_id' => StudyDestination::factory(),
            'section' => 'why',
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(12),
            'needs_verification' => false,
            'sort_order' => 0,
        ];
    }
}
