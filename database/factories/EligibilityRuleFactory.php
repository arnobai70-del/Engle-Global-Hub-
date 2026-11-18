<?php

namespace Database\Factories;

use App\Models\EligibilityRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Test data only: the limits are arbitrary numbers, not real admission rules.
 *
 * @extends Factory<EligibilityRule>
 */
class EligibilityRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'study_level' => fake()->randomElement(['foundation', 'diploma', 'bachelor', 'master']),
            'qualifications' => ['hsc'],
            'min_gpa' => null,
            'min_english' => null,
            'max_gap_years' => null,
            'note_en' => null,
            'note_bn' => null,
            'is_active' => true,
            'sort_order' => 10,
        ];
    }
}
