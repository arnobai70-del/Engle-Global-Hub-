<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Success stories (testimonials) show the student's intake, for example "September 2025".
 * Optional; existing rows are not touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            $table->string('intake', 40)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            $table->dropColumn('intake');
        });
    }
};
