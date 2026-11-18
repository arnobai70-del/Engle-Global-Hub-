<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rules for the public eligibility checker, entered by staff in Admin › Eligibility rules.
 * Each rule says which course level a student may apply for, given their qualification,
 * result, English score and study gap. Empty limits mean "no limit"; there are no defaults.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_rules', function (Blueprint $table) {
            $table->id();
            $table->string('study_level', 20);
            $table->json('qualifications');
            $table->decimal('min_gpa', 3, 2)->nullable();
            $table->decimal('min_english', 2, 1)->nullable();
            $table->unsignedTinyInteger('max_gap_years')->nullable();
            $table->string('note_en', 300)->nullable();
            $table->string('note_bn', 300)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(10);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_rules');
    }
};
