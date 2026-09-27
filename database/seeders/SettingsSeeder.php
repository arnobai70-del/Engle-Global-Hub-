<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    /**
     * Seed the application's default settings.
     */
    public function run(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore([
            [
                'group' => 'general',
                'key' => 'site_name',
                'value' => 'Eagle Global Hub LTD',
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'general',
                'key' => 'logo_path',
                'value' => 'images/eagle-global-hub-logo.png',
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'phone',
                'value' => '01953626481',
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'email',
                'value' => 'info@eagleglobalhub.com',
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'contact',
                'key' => 'address',
                'value' => 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212',
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'localization',
                'key' => 'locale',
                'value' => config('app.locale', 'en'),
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'localization',
                'key' => 'timezone',
                'value' => config('app.timezone', 'UTC'),
                'type' => 'string',
                'is_public' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
