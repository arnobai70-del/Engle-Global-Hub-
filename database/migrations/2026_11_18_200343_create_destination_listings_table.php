<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where to stay, where to eat and things to do at an Explore Bangladesh destination.
 * Entered and verified by staff; shown only once published.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('name', 150);
            $table->string('area', 150)->nullable();
            $table->string('price_range', 10)->nullable();
            $table->text('description')->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['destination_id', 'is_published', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_listings');
    }
};
