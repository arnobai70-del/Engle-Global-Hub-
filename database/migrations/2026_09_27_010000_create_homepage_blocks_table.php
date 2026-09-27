<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('section', 60)->index();
            $table->string('key', 120)->nullable();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('image_alt')->nullable();
            $table->text('icon')->nullable();
            $table->string('url', 500)->nullable();
            $table->string('cta_label', 120)->nullable();
            $table->json('meta')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['section', 'key'], 'homepage_blocks_section_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_blocks');
    }
};
