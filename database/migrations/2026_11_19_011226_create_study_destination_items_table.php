<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The list rows of a study destination page: reasons, subjects, entry requirements,
 * documents, visa steps, costs, work rules, FAQ and official sources. Factual rows carry
 * their own official source (URL and label), shown as "Source: …".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_destination_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('study_destination_id')->constrained()->cascadeOnDelete();
            $table->string('section', 30);
            $table->string('title', 300)->nullable();
            $table->string('title_bn', 300)->nullable();
            $table->text('body')->nullable();
            $table->text('body_bn')->nullable();
            $table->string('amount', 80)->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->string('source_label', 160)->nullable();
            $table->boolean('needs_verification')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['study_destination_id', 'section', 'sort_order'], 'study_items_section_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_destination_items');
    }
};
