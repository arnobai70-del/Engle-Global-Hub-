<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->string('name', 200);
            $table->string('study_level', 40)->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            // Free text copied from the institution's own published information.
            $table->string('tuition_note', 300)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['institution_id', 'name']);
        });

        Schema::create('course_intakes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('application_deadline')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
            $table->unique(['course_id', 'starts_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_intakes');
        Schema::dropIfExists('courses');
    }
};
