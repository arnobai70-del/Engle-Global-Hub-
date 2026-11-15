<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applications', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('course_name', 200)->nullable();
            $table->string('intake', 40)->nullable();
            $table->string('status', 40)->default('new_enquiry');
            $table->foreignId('assigned_consultant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'updated_at']);
            $table->index(['destination_country_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_applications');
    }
};
