<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enquiries', function (Blueprint $table): void {
            $table->foreignId('referred_by_agent_id')->nullable()->after('lead_source')->constrained('agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_enquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('referred_by_agent_id');
        });
    }
};
