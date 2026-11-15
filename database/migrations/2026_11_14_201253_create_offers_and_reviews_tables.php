<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 160)->unique();
            $table->string('service', 40)->nullable();
            $table->string('summary', 300);
            $table->text('terms')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_published', 'valid_until']);
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service', 40)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 120);
            $table->text('body');
            // Public display name chosen by the reviewer; never their email.
            $table->string('display_name', 80);
            $table->string('status', 20)->default('pending');
            $table->foreignId('moderated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('offers');
    }
};
