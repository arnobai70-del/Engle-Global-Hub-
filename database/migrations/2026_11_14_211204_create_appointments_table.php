<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_application_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('staff_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('mode', 20);
            $table->string('status', 20)->default('requested');
            // Shown to the student (office address or meeting link).
            $table->string('location_or_link', 500)->nullable();
            $table->string('student_note', 1000)->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['staff_user_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
