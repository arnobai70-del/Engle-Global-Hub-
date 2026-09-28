<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore([
            [
                'group' => 'analytics',
                'key' => 'meta_pixel_enabled',
                'value' => '0',
                'type' => 'boolean',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'analytics',
                'key' => 'meta_pixel_id',
                'value' => null,
                'type' => 'string',
                'is_public' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'analytics')
            ->whereIn('key', ['meta_pixel_enabled', 'meta_pixel_id'])
            ->delete();
    }
};
