<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('region', 150)->nullable();
            $table->string('summary', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image_path', 255)->nullable();
            $table->string('cover_image_alt', 200)->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 170)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['country_id', 'name']);
            $table->index(['is_published', 'sort_order']);
        });

        Schema::create('destination_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('path', 255);
            $table->string('alt_text', 200);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_images');
        Schema::dropIfExists('destinations');
    }
};
