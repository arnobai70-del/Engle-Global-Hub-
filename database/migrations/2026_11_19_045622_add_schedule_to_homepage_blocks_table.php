<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional show-from / show-until window for homepage blocks. Used by the homepage news
 * ticker headlines (section "news"); every other block leaves both empty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table): void {
            $table->timestamp('starts_at')->nullable()->after('is_active');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_blocks', function (Blueprint $table): void {
            $table->dropColumn(['starts_at', 'ends_at']);
        });
    }
};
