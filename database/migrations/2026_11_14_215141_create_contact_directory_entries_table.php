<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_directory_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 20)->default('phone');
            $table->string('label', 80);
            $table->string('contact_name', 120)->nullable();
            $table->string('department', 120)->nullable();
            $table->string('phone', 32)->unique();
            $table->string('availability', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_directory_entries');
    }
};
