<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_application_status_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            // Shown to the student on their timeline; internal notes stay staff-only.
            $table->string('student_note', 1000)->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['student_application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_application_status_changes');
    }
};
