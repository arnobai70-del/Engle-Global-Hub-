<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_applications', function (Blueprint $table): void {
            $table->date('follow_up_at')->nullable()->after('assigned_consultant_id');
        });

        Schema::table('agents', function (Blueprint $table): void {
            // "agent" (existing) or "b2b_partner" (Egabe B2B portal).
            $table->string('partner_type', 20)->default('agent')->after('company_name');
            $table->string('website_url', 2048)->nullable()->after('partner_type');
        });

        Schema::table('service_enquiries', function (Blueprint $table): void {
            $table->string('category', 40)->nullable()->after('service');
            $table->foreignId('referred_by_agent_id')->nullable()->after('user_id')->constrained('agents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_enquiries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('referred_by_agent_id');
            $table->dropColumn('category');
        });
        Schema::table('agents', function (Blueprint $table): void {
            $table->dropColumn(['partner_type', 'website_url']);
        });
        Schema::table('student_applications', function (Blueprint $table): void {
            $table->dropColumn('follow_up_at');
        });
    }
};
