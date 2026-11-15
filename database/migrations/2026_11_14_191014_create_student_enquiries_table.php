<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('full_name', 150);
            $table->string('email', 254);
            $table->string('phone', 32);
            $table->foreignId('destination_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('highest_qualification', 120)->nullable();
            $table->string('study_level', 40)->nullable();
            $table->string('preferred_intake', 40)->nullable();
            $table->boolean('wants_consultation')->default(false);
            $table->text('message')->nullable();
            $table->string('lead_source', 40)->default('direct');
            $table->dateTime('consented_at');
            $table->string('status', 40)->default('new');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('follow_up_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enquiries');
    }
};
