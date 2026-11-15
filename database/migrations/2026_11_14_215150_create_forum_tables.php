<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('description', 500)->nullable();
            $table->string('visibility', 20)->default('public');
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('forum_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->string('slug', 120)->unique();
            $table->string('status', 20)->default('visible');
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('last_posted_at')->nullable();
            $table->timestamps();
            $table->index(['forum_category_id', 'status', 'last_posted_at']);
        });

        Schema::create('forum_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->char('body_hash', 64);
            $table->string('status', 20)->default('visible');
            $table->timestamps();
            $table->index(['forum_thread_id', 'status']);
            $table->index(['user_id', 'body_hash']);
        });

        Schema::create('forum_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 500);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['forum_post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reports');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_threads');
        Schema::dropIfExists('forum_categories');
    }
};
