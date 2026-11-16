<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Institutions stay private CRM records unless the owner verifies one for the public Institutions page.
     */
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('status');
            $table->dateTime('publicly_verified_at')->nullable()->after('is_public');
            $table->index(['is_public', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table): void {
            $table->dropIndex(['is_public', 'status']);
            $table->dropColumn(['is_public', 'publicly_verified_at']);
        });
    }
};
