<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_applications', function (Blueprint $table): void {
            $table->foreignId('course_id')->nullable()->after('institution_id')->constrained()->nullOnDelete();
            $table->foreignId('course_intake_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('course_intake_id');
            $table->dropConstrainedForeignId('course_id');
        });
    }
};
