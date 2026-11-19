<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Study destinations other than Malaysia and Singapore (/study-in-{slug}). One row per
 * country; name and ISO code come from the countries table. Every section is filled in
 * by the owner from official sources; a page is public only when it is published and
 * complete (see StudyDestination::publishBlockers()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug', 80)->unique();
            $table->string('name_bn', 120)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('hero_title', 160)->nullable();
            $table->string('hero_title_bn', 160)->nullable();
            $table->text('intro')->nullable();
            $table->text('intro_bn')->nullable();
            $table->foreignId('hero_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('hero_alt', 200)->nullable();
            $table->string('hero_alt_bn', 200)->nullable();
            $table->string('visa_name', 120)->nullable();
            $table->string('visa_name_bn', 120)->nullable();
            $table->string('visa_authority', 160)->nullable();
            $table->string('visa_authority_bn', 160)->nullable();
            $table->string('processing_time', 200)->nullable();
            $table->string('processing_time_bn', 200)->nullable();
            $table->string('processing_source_url', 2048)->nullable();
            $table->string('processing_source_label', 160)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->decimal('bdt_rate', 12, 4)->nullable();
            $table->date('bdt_rate_checked_on')->nullable();
            $table->string('intakes', 200)->nullable();
            $table->string('intakes_bn', 200)->nullable();
            $table->timestamp('sources_seeded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_destinations');
    }
};
