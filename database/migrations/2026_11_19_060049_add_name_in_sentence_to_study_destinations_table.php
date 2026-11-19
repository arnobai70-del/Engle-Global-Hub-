<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How the country reads inside an English sentence ("the United Kingdom", "the Netherlands").
 * Optional: empty means the plain country name. Bengali is not affected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_destinations', function (Blueprint $table): void {
            $table->string('name_in_sentence', 120)->nullable()->after('name_bn');
        });
    }

    public function down(): void
    {
        Schema::table('study_destinations', function (Blueprint $table): void {
            $table->dropColumn('name_in_sentence');
        });
    }
};
