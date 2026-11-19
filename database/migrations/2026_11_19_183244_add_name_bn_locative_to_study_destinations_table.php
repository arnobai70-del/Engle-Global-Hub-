<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Bengali country name with its place ending (locative), for Bengali sentences:
 * বুলগেরিয়া → বুলগেরিয়ায়, জাপান → জাপানে. Optional: the correct form cannot be made by a rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_destinations', function (Blueprint $table): void {
            $table->string('name_bn_locative', 120)->nullable()->after('name_in_sentence');
        });
    }

    public function down(): void
    {
        Schema::table('study_destinations', function (Blueprint $table): void {
            $table->dropColumn('name_bn_locative');
        });
    }
};
