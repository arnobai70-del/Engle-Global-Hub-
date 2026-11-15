<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use Illuminate\Database\Seeder;

/**
 * Singapore study guide topics as unpublished drafts with titles only. Staff
 * add verified text and official sources before publishing; nothing about
 * immigration, fees or admission is invented here. Existing entries are kept.
 */
class SingaporeGuideSeeder extends Seeder
{
    /** @var array<string, string> slug => title */
    private const GUIDES = [
        'students-pass-guidance' => "Singapore Student's Pass Guidance",
        'college-admission' => 'Singapore College Admission',
        'university-admission' => 'Singapore University Admission',
        'course-selection' => 'Choosing a Course in Singapore',
        'tuition-fees' => 'Singapore Tuition Fees',
        'student-accommodation' => 'Singapore Student Accommodation',
        'pre-departure' => 'Singapore Pre-Departure Checklist',
    ];

    public function run(): void
    {
        $position = 0;
        foreach (self::GUIDES as $slug => $title) {
            $entry = ContentEntry::query()->firstOrNew(['type' => ContentType::Guide->value, 'slug' => $slug]);
            if (! $entry->exists) {
                $entry->fill(['title' => $title, 'sort_order' => ++$position]);
                $entry->status = ContentEntry::STATUS_DRAFT;
                $entry->save();
            }
        }
    }
}
