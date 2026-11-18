<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Http\Controllers\ExploreBangladeshController;
use App\Models\ContentEntry;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Database\Seeder;

/**
 * Explore Bangladesh destinations for foreign visitors, seeded as UNPUBLISHED drafts:
 * the owner checks each one, adds photos and publishes it in Admin › Destinations.
 * Only names, regions, categories and short summaries are seeded; no prices, phone
 * numbers, opening hours or ticket fees, and no hotels or restaurants.
 *
 * Safe to run again on the live site:
 * - a destination is created only when its slug (and its name) is not taken;
 * - an existing destination is never renamed, published or overwritten; only its
 *   still-empty new fields (category, Bengali text, tips) are filled in;
 * - the "explore-essentials" page section is created once, as a draft.
 */
class BangladeshDestinationSeeder extends Seeder
{
    private const NOTE = 'Seeded draft for Explore Bangladesh. Check the text, add photos and publish when ready.';

    public function run(): void
    {
        $bangladeshId = Country::query()->where('iso2', 'BD')->value('id');
        if ($bangladeshId === null) {
            return;
        }

        foreach (self::destinations() as $position => $data) {
            $this->place((int) $bangladeshId, $position, $data);
        }

        $this->essentialsDraft();
    }

    /**
     * @param  array<string, string>  $data
     */
    private function place(int $countryId, int $position, array $data): void
    {
        $existing = Destination::query()->where('slug', $data['slug'])->first();

        if ($existing === null) {
            if (Destination::query()->where('country_id', $countryId)->where('name', $data['name'])->exists()) {
                return;
            }
            $destination = new Destination([...$data, 'country_id' => $countryId, 'sort_order' => ($position + 1) * 10]);
            $destination->is_published = false;
            $destination->is_featured = false;
            $destination->save();

            return;
        }

        // Fill only what is still empty; the name, text the admin wrote and the publish state stay as they are.
        foreach (['name_bn', 'region', 'category', 'summary', 'summary_bn', 'travel_tips'] as $field) {
            if (blank($existing->getAttribute($field)) && filled($data[$field] ?? null)) {
                $existing->setAttribute($field, $data[$field]);
            }
        }
        if ($existing->isDirty()) {
            $existing->save();
        }
    }

    private function essentialsDraft(): void
    {
        if (ContentEntry::query()->where('type', ContentType::PageSection->value)->where('slug', 'explore-essentials')->exists()) {
            return;
        }

        $entry = new ContentEntry([
            'slug' => 'explore-essentials',
            'title' => 'Explore Bangladesh: travel essentials for foreigners (one "- Title: text" per line)',
            'summary' => 'Shown in the "Travel essentials for foreigners" section. Until this is published, the built-in text is shown.',
            'body' => collect(ExploreBangladeshController::DEFAULT_ESSENTIALS)->map(fn (array $item): string => '- '.$item[0].': '.$item[1])->implode("\n"),
            'title_bn' => 'বাংলাদেশ ঘুরে দেখুন: বিদেশিদের জন্য ভ্রমণ তথ্য',
            'body_bn' => implode("\n", [
                '- ভিসা: ভ্রমণের আগে আপনার দেশের নাগরিকদের জন্য বাংলাদেশের ই-ভিসা ও অন-অ্যারাইভাল ভিসার অফিসিয়াল নিয়ম দেখে নিন। প্রক্রিয়া বোঝাতে ও কাগজপত্রে আমরা সাহায্য করতে পারি।',
                '- মুদ্রা: বাংলাদেশের মুদ্রা টাকা (BDT)। ব্যাংক বা অনুমোদিত মানি চেঞ্জার থেকে, বিমানবন্দরেও, টাকা বদলাতে পারবেন।',
                '- নগদ ও কার্ড: শহরের বড় হোটেল, রেস্তোরাঁ ও দোকানে কার্ড চলে। বাজার, স্থানীয় যানবাহন ও গ্রামাঞ্চলের জন্য নগদ টাকা রাখুন।',
                '- সিম কার্ড ও মোবাইল ডেটা: বিমানবন্দর ও অপারেটরের দোকানে ডেটাসহ লোকাল সিম পাওয়া যায়; নিবন্ধনের জন্য পাসপোর্ট সাথে রাখুন।',
                '- যাতায়াত: ঢাকা থেকে প্রধান শহরগুলোতে অভ্যন্তরীণ ফ্লাইট আছে। ট্রেন ও দূরপাল্লার বাস সাশ্রয়ী, আর চালকসহ গাড়ি ভাড়া সবচেয়ে আরামদায়ক।',
                '- ভ্রমণের সেরা সময়: মোটামুটি নভেম্বর থেকে ফেব্রুয়ারি শীতল ও শুষ্ক মৌসুম, ভ্রমণের জন্য সবচেয়ে আরামদায়ক। প্রায় জুন থেকে সেপ্টেম্বর বর্ষায় ভারী বৃষ্টি হয়।',
                '- পোশাক ও আদবকায়দা: বিশেষ করে মসজিদ, মন্দির ও গ্রামাঞ্চলে শালীন পোশাক পরুন। বলা হলে জুতা খুলুন এবং মানুষের ছবি তোলার আগে অনুমতি নিন।',
                '- নিরাপত্তা ও জরুরি সেবা: পুলিশ, ফায়ার সার্ভিস বা অ্যাম্বুলেন্সের জন্য জাতীয় জরুরি নম্বর ৯৯৯-এ ফোন করুন। পাসপোর্ট ও ভিসার কপি রাখুন এবং হোটেল বা গাইডকে আপনার পরিকল্পনা জানিয়ে রাখুন।',
            ]),
        ]);
        $entry->type = ContentType::PageSection;
        $entry->status = ContentEntry::STATUS_DRAFT;
        $entry->review_status = 'needs_owner_review';
        $entry->review_notes = self::NOTE.' Bengali: native-speaker review recommended.';
        $entry->save();
    }

    /**
     * The three slugs of the first release (coxs-bazar, sylhet, kuakata) are kept, so live
     * records are matched instead of duplicated.
     *
     * @return list<array<string, string>>
     */
    public static function destinations(): array
    {
        return [
            ['slug' => 'sundarbans-mangrove-forest', 'name' => 'Sundarbans Mangrove Forest', 'name_bn' => 'সুন্দরবন ম্যানগ্রোভ বন', 'region' => 'Khulna', 'category' => 'nature',
                'summary' => 'UNESCO World Heritage mangrove forest and home of the Bengal tiger, visited on boat tours from Khulna or Mongla.',
                'summary_bn' => 'ইউনেস্কো বিশ্ব ঐতিহ্যবাহী ম্যানগ্রোভ বন ও রয়েল বেঙ্গল টাইগারের আবাস; খুলনা বা মংলা থেকে নৌকা ভ্রমণে যাওয়া হয়।'],
            ['slug' => 'coxs-bazar', 'name' => "Cox's Bazar Beach", 'name_bn' => 'কক্সবাজার সমুদ্রসৈকত', 'region' => 'Chattogram', 'category' => 'beach',
                'summary' => "One of the world's longest natural sea beaches.",
                'summary_bn' => 'বিশ্বের দীর্ঘতম প্রাকৃতিক সমুদ্রসৈকতগুলোর একটি।'],
            ['slug' => 'saint-martins-island', 'name' => "Saint Martin's Island", 'name_bn' => 'সেন্ট মার্টিন দ্বীপ', 'region' => 'Chattogram', 'category' => 'beach',
                'summary' => "Bangladesh's coral island in the Bay of Bengal.",
                'summary_bn' => 'বঙ্গোপসাগরে বাংলাদেশের প্রবাল দ্বীপ।',
                'travel_tips' => 'Visitor access to the island is restricted by the government. Check the current rules before planning your trip.'],
            ['slug' => 'sreemangal-tea-gardens', 'name' => 'Sreemangal Tea Gardens', 'name_bn' => 'শ্রীমঙ্গল চা-বাগান', 'region' => 'Sylhet', 'category' => 'hills',
                'summary' => 'The "tea capital" of Bangladesh, with tea estates and Lawachara National Park.',
                'summary_bn' => 'বাংলাদেশের "চায়ের রাজধানী", চা-বাগান ও লাউয়াছড়া জাতীয় উদ্যানসহ।'],
            ['slug' => 'sylhet', 'name' => 'Sylhet (Ratargul Swamp Forest & Jaflong)', 'name_bn' => 'সিলেট (রাতারগুল জলাবন ও জাফলং)', 'region' => 'Sylhet', 'category' => 'nature',
                'summary' => 'A freshwater swamp forest and river and hill scenery near the Indian border.',
                'summary_bn' => 'মিঠা পানির জলাবন, আর ভারত সীমান্তের কাছে নদী ও পাহাড়ের দৃশ্য।'],
            ['slug' => 'bandarban-hill-tracts', 'name' => 'Bandarban Hill Tracts', 'name_bn' => 'বান্দরবান পার্বত্য অঞ্চল', 'region' => 'Chattogram', 'category' => 'hills',
                'summary' => 'Hills, waterfalls and indigenous communities in the Chittagong Hill Tracts.',
                'summary_bn' => 'পার্বত্য চট্টগ্রামের পাহাড়, ঝরনা ও আদিবাসী জনগোষ্ঠী।',
                'travel_tips' => 'Some areas need a permit for foreign visitors. Check the current rules before you travel.'],
            ['slug' => 'rangamati-kaptai-lake', 'name' => 'Rangamati & Kaptai Lake', 'name_bn' => 'রাঙামাটি ও কাপ্তাই হ্রদ', 'region' => 'Chattogram', 'category' => 'hills',
                'summary' => 'Lake and hill landscapes, explored on boat trips.',
                'summary_bn' => 'হ্রদ ও পাহাড়ের দৃশ্য, ঘুরে দেখা যায় নৌকা ভ্রমণে।'],
            ['slug' => 'kuakata', 'name' => 'Kuakata Sea Beach', 'name_bn' => 'কুয়াকাটা সমুদ্রসৈকত', 'region' => 'Barishal', 'category' => 'beach',
                'summary' => 'A beach where you can watch both sunrise and sunset over the sea.',
                'summary_bn' => 'যে সৈকতে সমুদ্রের ওপর সূর্যোদয় ও সূর্যাস্ত দুটোই দেখা যায়।'],
            ['slug' => 'somapura-mahavihara-paharpur', 'name' => 'Somapura Mahavihara (Paharpur)', 'name_bn' => 'সোমপুর মহাবিহার (পাহাড়পুর)', 'region' => 'Rajshahi', 'category' => 'historical',
                'summary' => 'UNESCO World Heritage ruins of a great Buddhist monastery.',
                'summary_bn' => 'একটি বিশাল বৌদ্ধ বিহারের ধ্বংসাবশেষ, ইউনেস্কো বিশ্ব ঐতিহ্য।'],
            ['slug' => 'mosque-city-of-bagerhat', 'name' => 'Historic Mosque City of Bagerhat', 'name_bn' => 'ঐতিহাসিক মসজিদের শহর বাগেরহাট', 'region' => 'Khulna', 'category' => 'historical',
                'summary' => 'UNESCO World Heritage site including the Sixty Dome Mosque (Shat Gombuj Masjid).',
                'summary_bn' => 'ষাট গম্বুজ মসজিদসহ ইউনেস্কো বিশ্ব ঐতিহ্যবাহী স্থান।'],
            ['slug' => 'mahasthangarh', 'name' => 'Mahasthangarh', 'name_bn' => 'মহাস্থানগড়', 'region' => 'Rajshahi', 'category' => 'historical',
                'summary' => 'One of the oldest archaeological sites in Bangladesh, near Bogura.',
                'summary_bn' => 'বগুড়ার কাছে বাংলাদেশের প্রাচীনতম প্রত্নস্থানগুলোর একটি।'],
            ['slug' => 'old-dhaka', 'name' => 'Old Dhaka (Lalbagh Fort, Ahsan Manzil, Star Mosque)', 'name_bn' => 'পুরান ঢাকা (লালবাগ কেল্লা, আহসান মঞ্জিল, তারা মসজিদ)', 'region' => 'Dhaka', 'category' => 'historical',
                'summary' => 'A Mughal-era fort, the Pink Palace and the lanes and food of the old city.',
                'summary_bn' => 'মোগল আমলের কেল্লা, গোলাপি প্রাসাদ, আর পুরান শহরের গলি ও খাবার।'],
            ['slug' => 'sonargaon-panam-nagar', 'name' => 'Sonargaon & Panam Nagar', 'name_bn' => 'সোনারগাঁও ও পানাম নগর', 'region' => 'Dhaka', 'category' => 'historical',
                'summary' => 'The old capital of Bengal and its historic merchant town.',
                'summary_bn' => 'বাংলার পুরোনো রাজধানী ও এর ঐতিহাসিক বণিক নগরী।'],
            ['slug' => 'puthia-temple-complex', 'name' => 'Puthia Temple Complex', 'name_bn' => 'পুঠিয়া মন্দির কমপ্লেক্স', 'region' => 'Rajshahi', 'category' => 'religious',
                'summary' => 'A cluster of historic Hindu temples.',
                'summary_bn' => 'ঐতিহাসিক হিন্দু মন্দিরের একটি সমষ্টি।'],
        ];
    }
}
