<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Management team as listed in the business requirements document. Seeded as
 * unpublished drafts until the owner verifies names and roles; re-seeding
 * never republishes or overwrites edited records.
 */
class TeamMemberSeeder extends Seeder
{
    /** @var list<array{name: string, role_title: string}> */
    private const MEMBERS = [
        ['name' => 'Tajul Islam', 'role_title' => 'Managing Director'],
        ['name' => 'MD. Jahirul Islam Jubel', 'role_title' => 'Founder & CEO'],
        ['name' => 'MD. Jahirul Islam', 'role_title' => 'Finance Director'],
        ['name' => 'Sharmeen Akter Rony', 'role_title' => 'Executive Director'],
        ['name' => 'Shoaib Akter / Shoaib', 'role_title' => 'Marketing Director / Customer (CRM)'],
    ];

    public function run(): void
    {
        foreach (self::MEMBERS as $position => $member) {
            TeamMember::query()->firstOrCreate(
                ['name' => $member['name'], 'role_title' => $member['role_title']],
                ['sort_order' => $position + 1, 'is_published' => false],
            );
        }
    }
}
