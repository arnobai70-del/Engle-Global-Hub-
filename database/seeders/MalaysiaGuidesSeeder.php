<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

/**
 * Three Malaysia study guides, written only from official EMGS (Education Malaysia
 * Global Services) pages, which are stored as each guide's sources. Runs once:
 * guides an admin later edits or deletes are never overwritten or re-created.
 */
class MalaysiaGuidesSeeder extends Seeder
{
    private const VERSION = '1';

    private const NOTE = 'Written from the official EMGS pages listed as sources. Check them again before each intake, as EMGS rules and fees can change.';

    public function run(SettingService $settings): void
    {
        if (rescue(fn () => (string) $settings->get('content', 'malaysia_guides_version'), '', false) === self::VERSION) {
            return;
        }

        foreach ($this->guides() as $position => $guide) {
            if (ContentEntry::query()->where('type', ContentType::Guide->value)->where('slug', $guide['slug'])->exists()) {
                continue;
            }

            $entry = new ContentEntry($guide + ['location' => 'Malaysia', 'sort_order' => ($position + 1) * 10]);
            $entry->type = ContentType::Guide;
            $entry->status = ContentEntry::STATUS_PUBLISHED;
            $entry->published_at = now();
            $entry->last_verified_at = now();
            $entry->review_notes = self::NOTE;
            $entry->save();
        }

        $settings->set('content', 'malaysia_guides_version', self::VERSION);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function guides(): array
    {
        $steps = 'https://educationmalaysia.gov.my/blogs/study-in-malaysia-step-by-step-how-to-apply-for-a-student-visa';
        $faq = 'https://educationmalaysia.gov.my/get-in-touch/faq';
        $guidelines = 'https://visa.educationmalaysia.gov.my/guidelines';
        $medical = 'https://visa.educationmalaysia.gov.my/guidelines/medical-screening';
        $fees = 'https://visa.educationmalaysia.gov.my/student-visa/calculate-application-fees';

        return [
            [
                'slug' => 'malaysia-student-pass-step-by-step',
                'category' => 'Student Pass',
                'title' => 'Malaysia Student Pass: the EMGS process step by step',
                'summary' => 'How the Student Pass works for new international students, from the online EMGS application to endorsement in Malaysia.',
                'body' => <<<'TXT'
A Student Pass is needed for programmes longer than 3 months. Applications are made online through EMGS (Education Malaysia Global Services) after you accept your offer.

## The steps
1. Apply online: create an account and apply for a Student Pass on the EMGS website.
2. Upload documents: upload all the required documents.
3. eVAL: the Immigration Department of Malaysia issues your electronic Visa Approval Letter (eVAL).
4. Single Entry Visa: apply for the Single Entry Visa. Bangladeshi nationals can apply for an e-Visa.
5. Arrival card: apply for the Malaysia Digital Arrival Card (MDAC) within 3 days before you arrive.
6. Travel to Malaysia.
7. Post-arrival medical screening at an EMGS panel clinic.
8. Student Pass endorsement by Malaysian Immigration. Your institution assists you.

## Good to know
- The eVAL is valid for 6 months from the date it is issued.
- You may only enter Malaysia after receiving the eVAL. Entering while it is still being processed is not permitted.
- EMGS's client charter is 14 working days for a complete application. Incomplete or incorrect documents can take longer.
- EMGS sets the fees. Check the current amounts on the official EMGS fee calculator.
TXT,
                'title_bn' => 'মালয়েশিয়ার স্টুডেন্ট পাস: ধাপে ধাপে EMGS প্রক্রিয়া',
                'summary_bn' => 'নতুন আন্তর্জাতিক শিক্ষার্থীদের জন্য স্টুডেন্ট পাস কীভাবে কাজ করে, অনলাইনে EMGS আবেদন থেকে মালয়েশিয়ায় এনডোর্সমেন্ট পর্যন্ত।',
                'body_bn' => <<<'TXT'
৩ মাসের বেশি মেয়াদি প্রোগ্রামের জন্য স্টুডেন্ট পাস লাগে। অফার গ্রহণের পর EMGS (এডুকেশন মালয়েশিয়া গ্লোবাল সার্ভিসেস)-এর মাধ্যমে অনলাইনে আবেদন করতে হয়।

## ধাপগুলো
1. অনলাইনে আবেদন: EMGS ওয়েবসাইটে অ্যাকাউন্ট খুলে স্টুডেন্ট পাসের আবেদন করুন।
2. কাগজপত্র আপলোড: প্রয়োজনীয় সব কাগজপত্র আপলোড করুন।
3. eVAL: মালয়েশিয়ার ইমিগ্রেশন ডিপার্টমেন্ট আপনার ইলেকট্রনিক ভিসা অ্যাপ্রুভাল লেটার (eVAL) দেয়।
4. সিঙ্গেল এন্ট্রি ভিসা: সিঙ্গেল এন্ট্রি ভিসার আবেদন করুন। বাংলাদেশি নাগরিকরা ই-ভিসার আবেদন করতে পারেন।
5. অ্যারাইভাল কার্ড: পৌঁছানোর ৩ দিনের মধ্যে আগে মালয়েশিয়া ডিজিটাল অ্যারাইভাল কার্ড (MDAC)-এর আবেদন করুন।
6. মালয়েশিয়ায় যাত্রা।
7. EMGS প্যানেল ক্লিনিকে পৌঁছানোর পরের মেডিকেল স্ক্রিনিং।
8. মালয়েশিয়ান ইমিগ্রেশনের মাধ্যমে স্টুডেন্ট পাস এনডোর্সমেন্ট। আপনার প্রতিষ্ঠান সাহায্য করবে।

## জেনে রাখুন
- eVAL ইস্যুর তারিখ থেকে ৬ মাস বৈধ।
- eVAL পাওয়ার পরই শুধু মালয়েশিয়ায় প্রবেশ করা যায়। প্রক্রিয়াধীন অবস্থায় প্রবেশের অনুমতি নেই।
- সম্পূর্ণ আবেদনের জন্য EMGS-এর ক্লায়েন্ট চার্টার ১৪ কর্মদিবস। অসম্পূর্ণ বা ভুল কাগজপত্রে বেশি সময় লাগতে পারে।
- ফি ঠিক করে EMGS। বর্তমান পরিমাণ অফিসিয়াল EMGS ফি ক্যালকুলেটরে দেখুন।
TXT,
                'source_urls' => [$steps, $faq, $fees],
            ],
            [
                'slug' => 'malaysia-student-pass-documents',
                'category' => 'Documents',
                'title' => 'Documents for the Malaysia Student Pass',
                'summary' => 'The documents EMGS asks new students for, including the English language certificate for the eVAL application.',
                'body' => <<<'TXT'
EMGS lists these documents for a new Student Pass application. If you apply through your institution, it may request them from you.

- Passport: the data page, every page with a visa and the observation page. EMGS asks for a passport valid for at least 18 months.
- One passport-size photo with a white background (35 mm × 45 mm).
- A copy of your offer letter from the university or college.
- The Health Declaration Form from EMGS.
- Academic certificates and transcripts for your previous studies.
- A personal bond, if your institution requires one. It is returned when you complete or withdraw from your studies, provided no immigration law is broken.

## English language certificate
Upload your English language certificate with your eVAL application. Accepted tests include MUET, IELTS, TOEFL iBT, TOEFL Essentials, PTE Academic, several Cambridge English exams and ELS CIEP Level. Without it, processing may be delayed and you may not receive a Student Pass for the full length of your course.
TXT,
                'title_bn' => 'মালয়েশিয়ার স্টুডেন্ট পাসের কাগজপত্র',
                'summary_bn' => 'নতুন শিক্ষার্থীদের কাছে EMGS যে কাগজপত্র চায়, eVAL আবেদনের জন্য ইংরেজি ভাষার সনদসহ।',
                'body_bn' => <<<'TXT'
নতুন স্টুডেন্ট পাস আবেদনের জন্য EMGS এই কাগজপত্রগুলোর তালিকা দেয়। প্রতিষ্ঠানের মাধ্যমে আবেদন করলে প্রতিষ্ঠান এগুলো আপনার কাছে চাইতে পারে।

- পাসপোর্ট: তথ্যের পাতা, ভিসাযুক্ত প্রতিটি পাতা ও অবজারভেশন পাতা। EMGS কমপক্ষে ১৮ মাস মেয়াদি পাসপোর্ট চায়।
- সাদা ব্যাকগ্রাউন্ডের একটি পাসপোর্ট সাইজের ছবি (৩৫ মিমি × ৪৫ মিমি)।
- বিশ্ববিদ্যালয় বা কলেজের অফার লেটারের কপি।
- EMGS-এর হেলথ ডিক্লারেশন ফর্ম।
- আগের পড়াশোনার শিক্ষাগত সনদ ও ট্রান্সক্রিপ্ট।
- প্রতিষ্ঠান চাইলে পার্সোনাল বন্ড। পড়াশোনা শেষ বা প্রত্যাহারের পর, কোনো ইমিগ্রেশন আইন ভঙ্গ না হলে, এটি ফেরত দেওয়া হয়।

## ইংরেজি ভাষার সনদ
eVAL আবেদনের সাথে ইংরেজি ভাষার সনদ আপলোড করুন। গ্রহণযোগ্য পরীক্ষার মধ্যে আছে MUET, IELTS, TOEFL iBT, TOEFL Essentials, PTE Academic, কয়েকটি Cambridge English পরীক্ষা ও ELS CIEP Level। এটি না দিলে প্রক্রিয়ায় দেরি হতে পারে এবং পুরো কোর্সের মেয়াদের জন্য স্টুডেন্ট পাস না-ও পেতে পারেন।
TXT,
                'source_urls' => [$guidelines],
            ],
            [
                'slug' => 'malaysia-arrival-medical-screening',
                'category' => 'Arrival',
                'title' => 'Arriving in Malaysia: arrival card, medical screening and endorsement',
                'summary' => 'What new students must do around arrival: the MDAC, the post-arrival medical screening, insurance and Student Pass endorsement.',
                'body' => <<<'TXT'
- Before you travel, apply for the Malaysia Digital Arrival Card (MDAC) within 3 days before your arrival.
- Insurance coverage is compulsory for all international students throughout their studies.
- After you arrive, attend the post-arrival medical screening at an EMGS panel clinic within 7 working days of your arrival date.
- If the screening is not completed, EMGS cannot begin the Student Pass endorsement.
- If you fail the medical screening, an appeal must be submitted within 3 days of receiving the notification.
- Your Student Pass is then endorsed by Malaysian Immigration. Your institution assists you with this.
TXT,
                'title_bn' => 'মালয়েশিয়ায় পৌঁছানো: অ্যারাইভাল কার্ড, মেডিকেল স্ক্রিনিং ও এনডোর্সমেন্ট',
                'summary_bn' => 'পৌঁছানোর আগে-পরে নতুন শিক্ষার্থীদের যা করতে হয়: MDAC, পৌঁছানোর পরের মেডিকেল স্ক্রিনিং, ইনস্যুরেন্স ও স্টুডেন্ট পাস এনডোর্সমেন্ট।',
                'body_bn' => <<<'TXT'
- যাত্রার আগে, পৌঁছানোর ৩ দিনের মধ্যে আগে মালয়েশিয়া ডিজিটাল অ্যারাইভাল কার্ড (MDAC)-এর আবেদন করুন।
- সব আন্তর্জাতিক শিক্ষার্থীর জন্য পুরো পড়াশোনার সময় ইনস্যুরেন্স কভারেজ বাধ্যতামূলক।
- পৌঁছানোর পর, পৌঁছানোর তারিখ থেকে ৭ কর্মদিবসের মধ্যে EMGS প্যানেল ক্লিনিকে মেডিকেল স্ক্রিনিং করান।
- স্ক্রিনিং সম্পন্ন না হলে EMGS স্টুডেন্ট পাস এনডোর্সমেন্ট শুরু করতে পারে না।
- মেডিকেল স্ক্রিনিংয়ে উত্তীর্ণ না হলে, নোটিফিকেশন পাওয়ার ৩ দিনের মধ্যে আপিল করতে হয়।
- এরপর মালয়েশিয়ান ইমিগ্রেশন আপনার স্টুডেন্ট পাস এনডোর্স করে। আপনার প্রতিষ্ঠান এতে সাহায্য করে।
TXT,
                'source_urls' => [$steps, $faq, $medical],
            ],
        ];
    }
}
