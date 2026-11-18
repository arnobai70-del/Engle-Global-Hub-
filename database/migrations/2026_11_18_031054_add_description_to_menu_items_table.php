<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A short description for homepage service tiles, editable in Admin › Menus.
 * Both columns are optional; existing rows are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('description_en', 300)->nullable()->after('label_bn');
            $table->string('description_bn', 300)->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['description_en', 'description_bn']);
        });
    }
};
