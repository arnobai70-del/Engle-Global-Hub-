<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\StudyDestination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Draft study destination pages (never published here) with only the official authority as a
 * suggested source, marked "verify before publishing". No visa rules, fees, processing times,
 * tuition, living costs or institution names are seeded: the owner adds them from official
 * sources. Safe to run repeatedly; existing pages and admin edits are never changed.
 */
class StudyDestinationSeeder extends Seeder
{
    /**
     * ISO code => [slug, suggested official sources [label, https URL]].
     *
     * @var array<string, array{0: string, 1: list<array{0: string, 1: string}>}>
     */
    private const DESTINATIONS = [
        'BG' => ['bulgaria', [['Ministry of Foreign Affairs of Bulgaria: visas', 'https://www.mfa.bg/en/']]],
        'GB' => ['united-kingdom', [['GOV.UK: Student visa', 'https://www.gov.uk/student-visa']]],
        'JP' => ['japan', [
            ['Immigration Services Agency of Japan', 'https://www.isa.go.jp/en/'],
            ['Ministry of Foreign Affairs of Japan: visas', 'https://www.mofa.go.jp/j_info/visit/visa/'],
        ]],
        'KR' => ['south-korea', [
            ['HiKorea (Korea Immigration Service)', 'https://www.hikorea.go.kr/'],
            ['Embassy of the Republic of Korea in Bangladesh', 'https://overseas.mofa.go.kr/bd-en/index.do'],
        ]],
        'CY' => ['cyprus', [['Civil Registry and Migration Department, Cyprus', 'https://www.moi.gov.cy/moi/crmd/crmd.nsf/']]],
    ];

    public function run(): void
    {
        $bengali = json_decode((string) @file_get_contents(lang_path('bn.json')), true) ?: [];

        foreach (self::DESTINATIONS as $iso => [$slug, $sources]) {
            $country = Country::query()->where('iso2', $iso)->first();
            if ($country === null) {
                $this->command?->warn("Study destinations: country {$iso} is missing, skipped.");

                continue;
            }

            $destination = StudyDestination::query()->firstOrCreate(['country_id' => $country->id], [
                'slug' => StudyDestination::query()->where('slug', $slug)->exists() ? $slug.'-'.Str::lower($iso) : $slug,
                'name_bn' => $bengali[$country->name] ?? null,
                'status' => StudyDestination::STATUS_DRAFT,
            ]);

            if (blank($destination->name_bn) && filled($bengali[$country->name] ?? null)) {
                $destination->forceFill(['name_bn' => $bengali[$country->name]])->save();
            }

            // Suggested sources only once: a source the owner removed is never added again.
            if ($destination->sources_seeded_at === null) {
                foreach ($sources as $position => [$label, $url]) {
                    if (! $destination->items()->where('section', 'source')->where('source_url', $url)->exists()) {
                        $destination->items()->create([
                            'section' => 'source', 'source_label' => $label.' (verify before publishing)', 'source_url' => $url,
                            'needs_verification' => true, 'sort_order' => ($position + 1) * 10,
                        ]);
                    }
                }
                $destination->forceFill(['sources_seeded_at' => now()])->save();
            }
        }

        $this->linkGuidesToCountries($bengali);
    }

    /**
     * Fills an empty study country on guides whose Location is exactly a country's English or
     * Bengali name (for guides added after the country_id column was created).
     *
     * @param  array<string, string>  $bengali
     */
    private function linkGuidesToCountries(array $bengali): void
    {
        $countries = [];
        foreach (Country::query()->get(['id', 'name']) as $country) {
            $countries[$country->name] = $country->id;
            if (filled($bengali[$country->name] ?? null)) {
                $countries[$bengali[$country->name]] ??= $country->id;
            }
        }

        ContentEntry::query()->where('type', ContentType::Guide->value)->whereNull('country_id')->whereNotNull('location')->get()
            ->each(function (ContentEntry $guide) use ($countries): void {
                $countryId = $countries[trim((string) $guide->location)] ?? null;
                if ($countryId !== null) {
                    $guide->forceFill(['country_id' => $countryId])->saveQuietly();
                }
            });
    }
}
