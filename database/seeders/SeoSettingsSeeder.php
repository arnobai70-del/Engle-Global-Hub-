<?php

namespace Database\Seeders;

use App\Services\SeoMetadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Empty SEO override slots for each managed public page. Blank values keep
 * the page defaults; existing values are never overwritten.
 */
class SeoSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];

        foreach (SeoMetadata::MANAGED_ROUTES as $route) {
            foreach (['title', 'description'] as $field) {
                $rows[] = [
                    'group' => 'seo', 'key' => $route.'.'.$field, 'value' => '', 'type' => 'string',
                    'is_public' => true, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }

        DB::table('settings')->insertOrIgnore($rows);
    }
}
