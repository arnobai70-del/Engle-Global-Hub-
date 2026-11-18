<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Course finder filters: a subject area and the yearly tuition in MYR as published by
 * the institution. Both are optional and entered in Admin › Courses & Intakes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('subject', 80)->nullable()->after('study_level');
            $table->unsignedInteger('tuition_fee_myr')->nullable()->after('tuition_note');
            $table->index('subject');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['subject']);
            $table->dropColumn(['subject', 'tuition_fee_myr']);
        });
    }
};
