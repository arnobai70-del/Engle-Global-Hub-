<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_programs', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('description', 1000)->nullable();
            $table->text('terms')->nullable();
            $table->text('earning_rules')->nullable();
            $table->unsignedSmallInteger('points_expire_after_days')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('reward_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_program_id')->constrained()->restrictOnDelete();
            $table->integer('points');
            $table->string('reason', 200);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'reward_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_transactions');
        Schema::dropIfExists('reward_programs');
    }
};
