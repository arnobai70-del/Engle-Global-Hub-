<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('role_title', 150);
            $table->text('bio')->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            // Drafts stay hidden until the owner has verified the name and role.
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['name', 'role_title']);
            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
