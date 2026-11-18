<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Explore Bangladesh for foreign visitors: a category for the filter, practical travel
 * fields, map coordinates, a "featured" flag and Bengali name/summary/description.
 * Additive only; every new column is optional and existing rows are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->string('category', 20)->nullable()->after('region');
            $table->string('best_time_to_visit', 150)->nullable()->after('description');
            $table->text('how_to_get_there')->nullable()->after('best_time_to_visit');
            $table->text('travel_tips')->nullable()->after('how_to_get_there');
            $table->decimal('latitude', 10, 7)->nullable()->after('travel_tips');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->string('name_bn', 150)->nullable()->after('name');
            $table->string('summary_bn', 500)->nullable()->after('summary');
            $table->text('description_bn')->nullable()->after('description');
            $table->index(['is_published', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropIndex(['is_published', 'category']);
            $table->dropColumn([
                'category', 'best_time_to_visit', 'how_to_get_there', 'travel_tips', 'latitude', 'longitude',
                'is_featured', 'name_bn', 'summary_bn', 'description_bn',
            ]);
        });
    }
};
