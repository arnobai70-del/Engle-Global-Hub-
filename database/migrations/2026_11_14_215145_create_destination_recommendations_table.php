<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destination_recommendations', function (Blueprint $table): void {
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommended_destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['destination_id', 'recommended_destination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_recommendations');
    }
};
