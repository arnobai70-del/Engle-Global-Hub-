<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Empty, admin-editable company content slots. Values are left blank for the
 * owner to write; no wording is invented here. Existing values are kept.
 */
class CompanyContentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore(array_map(fn (string $key): array => [
            'group' => 'company',
            'key' => $key,
            'value' => '',
            'type' => 'string',
            'is_public' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], ['mission', 'vision', 'why_choose_us_bn']));
    }
}
