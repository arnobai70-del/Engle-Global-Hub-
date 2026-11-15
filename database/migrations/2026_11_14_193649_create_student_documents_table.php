<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_application_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            // Display name only; the stored file uses a random name on a private disk.
            $table->string('original_name', 200);
            $table->string('stored_path', 255)->unique();
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('status', 20)->default('pending_review');
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // Shown to the student, for example the reason a document was rejected.
            $table->string('review_note', 1000)->nullable();
            $table->timestamps();
            $table->unique(['student_application_id', 'sha256']);
            $table->index(['student_application_id', 'document_type']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
