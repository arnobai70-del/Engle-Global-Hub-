<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

/**
 * Published starter content (Why Us sections and general FAQs) so the website is
 * not empty on first launch. It contains no claims that need owner approval:
 * no numbers, partnerships, guarantees, prices or testimonials.
 *
 * Runs once per content version: entries the admin later edits or deletes in
 * Admin › Website Content are never overwritten or re-created.
 */
class StarterContentSeeder extends Seeder
{
    /** Version 2 added the Malaysia FAQs (Malaysia became the main destination). */
    private const VERSION = 2;

    private const NOTE = 'Starter content added by the web team so the website is not empty. Edit or delete it in Admin › Website Content.';

    public function run(SettingService $settings): void
    {
        $done = (int) rescue(fn () => (string) $settings->get('content', 'starter_content_version'), '0', false);
        if ($done >= self::VERSION) {
            return;
        }

        // Only entries introduced after the version already seeded are added, so content an
        // admin edited or deleted earlier is never brought back.
        foreach ($this->entries() as $position => $entry) {
            $since = $entry['since'] ?? 1;
            unset($entry['since']);
            if ($since > $done) {
                $this->publish($entry, $position);
            }
        }

        $settings->set('content', 'starter_content_version', (string) self::VERSION);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function publish(array $data, int $position): void
    {
        $type = $data['type'];
        unset($data['type']);

        if (ContentEntry::query()->where('type', $type->value)->where('slug', $data['slug'])->exists()) {
            return;
        }

        $entry = new ContentEntry($data + ['sort_order' => ($position + 1) * 10]);
        $entry->type = $type;
        $entry->status = ContentEntry::STATUS_PUBLISHED;
        $entry->published_at = now();
        $entry->review_notes = self::NOTE;
        $entry->save();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function entries(): array
    {
        return [...$this->whyUs(), ...$this->faqs()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function whyUs(): array
    {
        return [
            [
                'type' => ContentType::PageSection,
                'slug' => 'why-us',
                'title' => 'Why Us: reasons (one "- Title: text" per line)',
                'body' => <<<'TXT'
- Honest advice: We explain what institutions and the authorities (EMGS in Malaysia, ICA in Singapore) require, and we never promise admission or a student visa.
- One consultant for your file: Follow your progress, documents and messages in your own student portal.
- Private documents: Your documents are stored privately and seen only by you and authorised staff.
- Clear costs: We point you to official fee pages and explain every payment we receive in writing.
- Travel help in one place: Visas, air tickets, hotels and tours are arranged by the same team that knows your plans.
- Bangla and English support: Ask your questions in the language you are comfortable with, in person, by phone or online.
TXT,
                'title_bn' => 'কেন আমরা: কারণসমূহ',
                'body_bn' => <<<'TXT'
- সৎ পরামর্শ: প্রতিষ্ঠান ও সিঙ্গাপুরের কর্তৃপক্ষ কী চায় তা আমরা ব্যাখ্যা করি, আর কখনো ভর্তি বা স্টুডেন্টস পাসের প্রতিশ্রুতি দিই না।
- আপনার ফাইলের জন্য একজন কনসালটেন্ট: নিজের স্টুডেন্ট পোর্টালে অগ্রগতি, কাগজপত্র ও বার্তা দেখুন।
- গোপন কাগজপত্র: আপনার কাগজপত্র গোপনে সংরক্ষিত থাকে এবং শুধু আপনি ও অনুমোদিত কর্মীরা দেখতে পারেন।
- স্পষ্ট খরচ: আমরা অফিসিয়াল ফি পেজ দেখিয়ে দিই এবং আমাদের গ্রহণ করা প্রতিটি পেমেন্ট লিখিতভাবে ব্যাখ্যা করি।
- এক জায়গায় ভ্রমণ সহায়তা: ভিসা, এয়ার টিকিট, হোটেল ও ট্যুর একই টিম ব্যবস্থা করে, যারা আপনার পরিকল্পনা জানে।
- বাংলা ও ইংরেজিতে সহায়তা: অফিসে, ফোনে বা অনলাইনে, যে ভাষায় স্বচ্ছন্দ সেই ভাষায় প্রশ্ন করুন।
TXT,
            ],
            [
                'type' => ContentType::PageSection,
                'slug' => 'why-us-promises',
                'title' => 'Why Us: what we will never do (one "- Title: text" per line)',
                'body' => <<<'TXT'
- No guarantees we cannot keep: Admission and visa decisions belong to institutions and governments. We never sell a "guaranteed" result.
- No false documents: We never create, alter or "arrange" documents. Every paper in your file must be genuine.
- No hidden charges: You know what you pay us and what you pay others before you decide.
- No pressure: A consultation is free and you decide in your own time.
TXT,
                'title_bn' => 'কেন আমরা: আমরা যা কখনো করি না',
                'body_bn' => <<<'TXT'
- রাখতে পারব না এমন নিশ্চয়তা নয়: ভর্তি ও ভিসার সিদ্ধান্ত প্রতিষ্ঠান ও সরকারের। আমরা কখনো "নিশ্চিত" ফলাফল বিক্রি করি না।
- ভুয়া কাগজপত্র নয়: আমরা কখনো কাগজপত্র তৈরি, পরিবর্তন বা "ব্যবস্থা" করি না। আপনার ফাইলের প্রতিটি কাগজ আসল হতে হবে।
- গোপন খরচ নয়: সিদ্ধান্ত নেওয়ার আগেই জানবেন আমাদের কত দেবেন আর অন্যদের কত।
- কোনো চাপ নয়: পরামর্শ বিনামূল্যে, আর সিদ্ধান্ত নেবেন নিজের সময়ে।
TXT,
            ],
            [
                'type' => ContentType::PageSection,
                'slug' => 'why-us-steps',
                'title' => 'Why Us: how it works (one "- Title: text" per line)',
                'body' => <<<'TXT'
- Free consultation: Tell us your goals, results and budget. We explain your options honestly.
- A clear plan: You receive a written checklist of documents, costs and deadlines.
- Application together: We prepare and review every form with you and track it in your portal.
- Ready to travel: Ticket, accommodation guidance and a pre-departure briefing.
TXT,
                'title_bn' => 'কেন আমরা: কীভাবে কাজ করি',
                'body_bn' => <<<'TXT'
- বিনামূল্যে পরামর্শ: আপনার লক্ষ্য, ফলাফল ও বাজেট বলুন। আমরা সৎভাবে আপনার বিকল্পগুলো ব্যাখ্যা করি।
- স্পষ্ট পরিকল্পনা: কাগজপত্র, খরচ ও সময়সীমার লিখিত চেকলিস্ট পাবেন।
- একসাথে আবেদন: প্রতিটি ফর্ম আপনার সাথে প্রস্তুত ও যাচাই করি এবং পোর্টালে ট্র্যাক করি।
- যাত্রার প্রস্তুতি: টিকিট, আবাসন বিষয়ক নির্দেশনা ও যাত্রাপূর্ব ব্রিফিং।
TXT,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function faqs(): array
    {
        $faq = static fn (string $slug, string $title, string $body, string $titleBn, string $bodyBn): array => [
            'type' => ContentType::Faq, 'slug' => $slug, 'title' => $title, 'body' => $body, 'title_bn' => $titleBn, 'body_bn' => $bodyBn,
        ];

        return [
            $faq('how-to-start', 'How do I start?',
                'Book a free consultation using the form on the Study in Malaysia page, call us, or visit our office. We look at your results, goals and budget and explain your options before you decide anything.',
                'আমি কীভাবে শুরু করব?',
                'স্টাডি ইন সিঙ্গাপুর পেজের ফর্ম দিয়ে বিনামূল্যে পরামর্শ বুক করুন, আমাদের ফোন করুন বা অফিসে আসুন। কোনো সিদ্ধান্ত নেওয়ার আগেই আমরা আপনার ফলাফল, লক্ষ্য ও বাজেট দেখে বিকল্পগুলো ব্যাখ্যা করি।'),
            $faq('consultation-free', 'Is the first consultation really free?',
                'Yes. The first consultation is free and there is no obligation to continue with us afterwards.',
                'প্রথম পরামর্শ কি সত্যিই বিনামূল্যে?',
                'হ্যাঁ। প্রথম পরামর্শ বিনামূল্যে, এবং এরপর আমাদের সাথে চালিয়ে যাওয়ার কোনো বাধ্যবাধকতা নেই।'),
            $faq('documents-needed', 'Which documents will I need?',
                'Usually a valid passport, academic certificates and transcripts, and financial documents. Some courses also ask for an English test result. The exact list is set by the institution and by ICA, so we give you a written checklist for your own application.',
                'কোন কোন কাগজপত্র লাগবে?',
                'সাধারণত বৈধ পাসপোর্ট, শিক্ষাগত সনদ ও ট্রান্সক্রিপ্ট এবং আর্থিক কাগজপত্র। কিছু কোর্সে ইংরেজি পরীক্ষার ফলাফলও লাগে। সঠিক তালিকা প্রতিষ্ঠান ও ICA ঠিক করে, তাই আপনার আবেদনের জন্য আমরা লিখিত চেকলিস্ট দিই।'),
            $faq('english-test', 'Do I need IELTS?',
                'It depends on the institution and the course. Some ask for IELTS or a similar test, others accept other proof of English. We check the current requirement with the institution before you apply.',
                'আমার কি IELTS লাগবে?',
                'এটা প্রতিষ্ঠান ও কোর্সের ওপর নির্ভর করে। কেউ IELTS বা সমমানের পরীক্ষা চায়, কেউ ইংরেজির অন্য প্রমাণ গ্রহণ করে। আবেদনের আগে আমরা প্রতিষ্ঠানের বর্তমান শর্ত যাচাই করি।'),
            $faq('how-long', 'How long does the process take?',
                'It depends on the intake and the institution. Start several months before the intake you want, so there is time for admission, the student visa and travel. After your consultation we give you a timeline for your own case.',
                'পুরো প্রক্রিয়ায় কত সময় লাগে?',
                'এটা ইনটেক ও প্রতিষ্ঠানের ওপর নির্ভর করে। পছন্দের ইনটেকের কয়েক মাস আগে শুরু করুন, যাতে ভর্তি, স্টুডেন্টস পাস ও যাত্রার জন্য সময় থাকে। পরামর্শের পর আপনার নিজের জন্য একটি সময়সূচি দিই।'),
            $faq('travel-help', 'Do you help with travel, not only study?',
                'Yes. Our travel desk helps with tourist and business visas, air tickets, hotels and tour packages. Send an enquiry from any service page and our team replies personally. Online booking will be added when a provider is connected.',
                'আপনারা কি শুধু পড়াশোনা নয়, ভ্রমণেও সাহায্য করেন?',
                'হ্যাঁ। আমাদের ট্রাভেল ডেস্ক ট্যুরিস্ট ও বিজনেস ভিসা, এয়ার টিকিট, হোটেল ও ট্যুর প্যাকেজে সাহায্য করে। যেকোনো সার্ভিস পেজ থেকে এনকোয়ারি পাঠান, আমাদের টিম নিজে উত্তর দেবে। প্রোভাইডার যুক্ত হলে অনলাইন বুকিং যোগ হবে।'),
            $faq('become-agent', 'How can I become an agent or B2B partner?',
                'Create an account and choose "Agent / B2B partner", then fill in the short partner application. Our team reviews every application, and the Agent Portal opens after approval.',
                'আমি কীভাবে এজেন্ট বা B2B পার্টনার হতে পারি?',
                'একটি অ্যাকাউন্ট খুলে "এজেন্ট / B2B পার্টনার" বেছে নিন, তারপর ছোট পার্টনার আবেদনটি পূরণ করুন। আমাদের টিম প্রতিটি আবেদন যাচাই করে, এবং অনুমোদনের পর এজেন্ট পোর্টাল খোলে।'),
            $faq('bangla-support', 'Can I talk to you in Bangla?',
                'Yes. You can talk to us in Bangla or English, and you can switch this website to বাংলা from the top of any page.',
                'আমি কি বাংলায় কথা বলতে পারি?',
                'হ্যাঁ। বাংলা বা ইংরেজিতে কথা বলতে পারেন, আর যেকোনো পেজের ওপর থেকে এই ওয়েবসাইট বাংলায় দেখতে পারেন।'),
            // Version 2: Malaysia is the main destination. Facts only from official EMGS pages.
            $faq('malaysia-student-pass-time', 'How long does the Malaysia Student Pass take?',
                'EMGS says its client charter is 14 working days for a complete application; incomplete or incorrect documents take longer. Your eVAL (visa approval letter) is valid for 6 months, and you may only enter Malaysia after you receive it.',
                'মালয়েশিয়ার স্টুডেন্ট পাসে কত সময় লাগে?',
                'EMGS জানায়, সম্পূর্ণ আবেদনের জন্য তাদের ক্লায়েন্ট চার্টার ১৪ কর্মদিবস; অসম্পূর্ণ বা ভুল কাগজপত্রে বেশি সময় লাগে। আপনার eVAL (ভিসা অনুমোদন পত্র) ৬ মাস বৈধ, এবং এটি পাওয়ার পরই শুধু মালয়েশিয়ায় প্রবেশ করা যায়।') + ['since' => 2, 'sort_order' => 5],
            $faq('malaysia-after-arrival', 'What happens after I arrive in Malaysia?',
                'You attend a medical screening at an EMGS panel clinic within 7 working days of arrival. After that, Malaysian Immigration endorses your Student Pass, and your institution helps you with this step.',
                'মালয়েশিয়ায় পৌঁছানোর পর কী হয়?',
                'পৌঁছানোর ৭ কর্মদিবসের মধ্যে EMGS প্যানেল ক্লিনিকে মেডিকেল স্ক্রিনিং করাতে হয়। এরপর মালয়েশিয়ান ইমিগ্রেশন আপনার স্টুডেন্ট পাস এনডোর্স করে, আর এই ধাপে আপনার প্রতিষ্ঠান সাহায্য করে।') + ['since' => 2, 'sort_order' => 6],
        ];
    }
}
