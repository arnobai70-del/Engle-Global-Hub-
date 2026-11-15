<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            // needs_source_verification | needs_owner_review | needs_native_review | approved (null = not tracked).
            $table->string('review_status', 40)->nullable()->after('status');
            $table->date('last_verified_at')->nullable()->after('review_status');
            // Internal reviewer notes; never shown publicly.
            $table->text('review_notes')->nullable()->after('last_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            $table->dropColumn(['review_status', 'last_verified_at', 'review_notes']);
        });
    }
};
