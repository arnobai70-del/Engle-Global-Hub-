<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Header menu: one "Student Visa" item instead of "Study in Malaysia" + "Study in Singapore".
 * Its mega-menu lists every study destination from Admin › Services & Destinations.
 * The Singapore item is only hidden (not deleted), so an admin can show it again in Admin › Menus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')->where('location', 'header')->whereNull('parent_id')->where('key', 'study-malaysia')
            ->update(['label_en' => 'Student Visa', 'label_bn' => 'স্টুডেন্ট ভিসা', 'updated_at' => now()]);
        // Give the item its own link, so it stays in the menu even when all its sub-links are hidden.
        DB::table('menu_items')->where('location', 'header')->whereNull('parent_id')->where('key', 'study-malaysia')->whereNull('target')
            ->update(['target' => 'route:services.show|student-visa']);
        DB::table('menu_items')->where('location', 'header')->whereNull('parent_id')->where('key', 'study')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        DB::table('menu_items')->where('location', 'header')->whereNull('parent_id')->where('key', 'study-malaysia')
            ->update(['label_en' => 'Study in Malaysia', 'label_bn' => 'মালয়েশিয়ায় পড়াশোনা', 'updated_at' => now()]);
        DB::table('menu_items')->where('location', 'header')->whereNull('parent_id')->where('key', 'study')
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
