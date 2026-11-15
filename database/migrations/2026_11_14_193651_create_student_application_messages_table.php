<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_application_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_user_id')->nullable()->constrained('users')->nullOnDelete();
            // "student" or "staff": who wrote it, independent of later role changes.
            $table->string('author_type', 16);
            $table->text('body');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['student_application_id', 'created_at'], 'sam_application_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_application_messages');
    }
};
