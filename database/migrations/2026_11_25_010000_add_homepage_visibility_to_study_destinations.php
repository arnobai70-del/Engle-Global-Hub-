<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Adds opt-in homepage country cards without changing the existing Student Visa
 * list. Existing destinations start off the homepage; admins opt them in.
 * Existing Singapore homepage tiles become inactive, not deleted, and can be
 * re-enabled deliberately in the Admin menu editor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_destinations', function (Blueprint $table): void {
            $table->boolean('show_on_homepage')->default(false);
        });

        // Hide ONLY homepage Singapore tiles; no Singapore services, country or study
        // pages are deleted. The owner can switch any of these tiles back on.
        DB::table('menu_items')->where('location', 'home_tiles')
            ->where(function ($query): void {
                $query->whereIn('key', ['tile-singapore', 'tile-study', 'tile-students-pass'])
                    ->orWhere('target', 'route:consultancy.singapore');
            })->update(['is_active' => false, 'updated_at' => now()]);

        // Japan is already part of CountrySeeder and StudyDestinationSeeder.
        // Backfill only if a live database was never seeded, preserving edits.
        $japanId = DB::table('countries')->where('iso2', 'JP')->value('id');
        if ($japanId === null) {
            $japanId = DB::table('countries')->insertGetId([
                'name' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN',
                'phone_code' => '+81', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        if (! DB::table('service_destinations')->where('service', 'student_visa')->where('country_id', $japanId)->exists()) {
            DB::table('service_destinations')->insert([
                'service' => 'student_visa', 'country_id' => $japanId,
                'is_active' => true, 'show_on_homepage' => false,
                'sort_order' => (int) DB::table('service_destinations')->where('service', 'student_visa')->max('sort_order') + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Give existing student-visa countries an editable draft template only
        // when they do not already have one. No claims are filled or published.
        $countries = DB::table('countries')
            ->join('service_destinations', 'countries.id', '=', 'service_destinations.country_id')
            ->where('service_destinations.service', 'student_visa')
            ->whereNotIn('countries.iso2', ['MY', 'SG'])
            ->select('countries.id', 'countries.iso2', 'countries.name')
            ->get();

        foreach ($countries as $country) {
            if (DB::table('study_destinations')->where('country_id', $country->id)->exists()) {
                continue;
            }
            $baseSlug = Str::slug($country->name);
            if ($baseSlug === '' || strlen($baseSlug) > 70) {
                $baseSlug = strtolower((string) $country->iso2);
            }
            $slug = $baseSlug;
            if (DB::table('study_destinations')->where('slug', $slug)->exists()) {
                $slug = substr($baseSlug, 0, 70).'-'.strtolower($country->iso2);
            }

            DB::table('study_destinations')->insert([
                'country_id' => $country->id, 'slug' => $slug,
                'status' => 'draft', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('service_destinations', function (Blueprint $table): void {
            $table->dropColumn('show_on_homepage');
        });

        // Keep owner-editable records, Japan and homepage tile choices intact.
        // Re-enabling Singapore or deleting countries on rollback would be unsafe.
    }
};
