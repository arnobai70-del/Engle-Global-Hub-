<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A guide's study country as a real reference instead of the free-text "Location".
 * Existing guides are backfilled only where Location exactly matches a country's English
 * name or its Bengali name from lang/bn.json; every other guide keeps country_id empty and
 * still falls back to the Location match.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            $table->foreignId('country_id')->nullable()->after('location')->constrained()->nullOnDelete();
        });

        $bengali = json_decode((string) @file_get_contents(lang_path('bn.json')), true) ?: [];
        $countries = [];
        foreach (DB::table('countries')->get(['id', 'name']) as $country) {
            $countries[$country->name] = $country->id;
            if (filled($bengali[$country->name] ?? null)) {
                $countries[$bengali[$country->name]] ??= $country->id;
            }
        }

        DB::table('content_entries')->where('type', 'guide')->whereNull('country_id')->whereNotNull('location')
            ->orderBy('id')->get(['id', 'location'])
            ->each(function (object $guide) use ($countries): void {
                $countryId = $countries[trim((string) $guide->location)] ?? null;
                if ($countryId !== null) {
                    DB::table('content_entries')->where('id', $guide->id)->update(['country_id' => $countryId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('content_entries', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
