<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\ContentEntry;
use App\Models\Destination;
use App\Models\ForumCategory;
use App\Models\RewardProgram;
use Illuminate\Database\Seeder;

/**
 * Review drafts for the CMS: Singapore guides, proposed company statements,
 * business service descriptions, FAQs, travel articles, destination texts,
 * forum categories and a proposed rewards framework.
 *
 * Safety rules:
 * - Nothing is published or activated here.
 * - Existing records are filled only while their body is still empty and they
 *   are drafts, so editor changes are never overwritten.
 * - Facts come from official sources checked on 15 Nov 2026 (listed per entry).
 *   Entries that rely on pages that could not be fetched directly are marked
 *   needs_source_verification.
 */
class ContentDraftSeeder extends Seeder
{
    private const CHECKED_ON = '2026-11-15';

    private const ICA_IHL = 'https://www.ica.gov.sg/information-and-e-services/long-term-visit-and-study/student-s-pass/student\'s-pass-application-renewal/institutes-of-higher-learning';

    private const ICA_PEI = 'https://www.ica.gov.sg/reside/STP/apply/pei';

    private const ICA_COLLECT = 'https://www.ica.gov.sg/reside/STP/collect';

    private const ICA_VISA = 'https://www.ica.gov.sg/enter-transit-depart/entering-singapore/visa_requirements';

    private const ICA_SGAC = 'https://www.ica.gov.sg/enter-transit-depart/entering-singapore/sg-arrival-card';

    private const MOE_TG = 'https://www.moe.gov.sg/financial-matters/tuition-grant-scheme/overview';

    private const MOE_TG_EMPLOYMENT = 'https://www.moe.gov.sg/financial-matters/tuition-grant-scheme/employment';

    private const MOE_ADMISSIONS = 'https://www.moe.gov.sg/post-secondary/admissions';

    private const MOE_DAE = 'https://www.moe.gov.sg/post-secondary/admissions/dae';

    private const CPE_EDUTRUST = 'https://www.cpe.gov.sg/for-students/edutrust-certification-status-of-peis';

    private const CPE_REGISTER = 'https://www.cpe.gov.sg/for-students/registration-status-of-private-education-institutions-peis-in-singapore';

    private const HDB_RENTING = 'https://www.hdb.gov.sg/renting-a-flat/renting-from-open-market/regulations';

    public function run(): void
    {
        foreach ($this->entries() as $entry) {
            $this->draft($entry);
        }

        $this->destinations();
        $this->forumCategories();
        $this->rewardsFramework();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function draft(array $data): void
    {
        $type = $data['type'];
        unset($data['type']);
        $review = $data['review'] ?? [];
        unset($data['review']);

        $entry = ContentEntry::query()->firstOrNew(['type' => $type->value, 'slug' => $data['slug']]);

        if ($entry->exists && ($entry->status !== ContentEntry::STATUS_DRAFT || trim((string) $entry->body) !== '')) {
            return;
        }

        $entry->fill($data);
        $entry->type = $type;
        $entry->status = ContentEntry::STATUS_DRAFT;
        $entry->review_status = $review['status'];
        $entry->review_notes = $review['notes'] ?? null;
        $entry->last_verified_at = $review['verified'] ?? null;
        $entry->save();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function entries(): array
    {
        return [...$this->guides(), ...$this->companyStatements(), ...$this->businessServices(), ...$this->faqs(), ...$this->inspiration()];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function guides(): array
    {
        return [
            [
                'type' => ContentType::Guide,
                'slug' => 'students-pass-guidance',
                'title' => "Singapore Student's Pass: Requirements and Application Steps",
                'seo_title' => "Singapore Student's Pass Requirements for Bangladeshi Students",
                'seo_description' => "Who needs a Singapore Student's Pass, the documents ICA asks for, when to apply, official fees, and what happens after In-Principle Approval.",
                'summary' => "Who needs a Student's Pass, which documents Singapore's Immigration & Checkpoints Authority (ICA) asks for, when to apply and what happens after approval.",
                'body' => <<<'TXT'
## Who needs a Student's Pass
Singapore's Immigration & Checkpoints Authority (ICA) says you must apply for a Student's Pass if you are accepted as a full-time student at an approved Institute of Higher Learning (a local university, a polytechnic or an approved offshore campus) or at an EduTrust-certified private education institution. Holders of a Dependant's Pass, Long-Term Visit Pass or Immigration Exemption Order do not need one.

## The institution comes first
You can only apply after an institution has offered you a place. The institution gives you the details you need to submit the application through ICA's online service.

## Documents ICA asks for
- Passport biodata page, and birth certificate if applicable
- Personal details: countries you have lived in, education, employment and financial support
- Details of parents, spouse and/or siblings, if applicable
- Singapore residential address, contact details and an email address
- A recent passport-sized digital colour photo taken within the last 3 months, following ICA's photo guidelines

ICA asks for original documents and official English translations of documents in other languages. Incomplete documents may lead to rejection.

## When to apply
ICA recommends applying 2 to 3 months before your course starts. You do not need to be in Singapore while the application is processed.

## Processing time and fees published by ICA
ICA says applications for Institutes of Higher Learning are processed within 1 week, or within 2 weeks if you need a visa to enter Singapore. Bangladesh is on ICA's list of visa-required countries. ICA also says some applications may take longer.

When we checked, ICA listed a non-refundable S$45 processing fee, an S$60 issuance fee and S$30 for a Multiple Journey Visa where applicable. Always confirm current fees on ICA's website before paying.

## After approval: the IPA letter
If your application is approved, ICA issues an In-Principle Approval (IPA) letter. You must complete the formalities before the IPA expires; if it expires you have to apply again. Our pre-departure guide explains the next steps.

## How Eagle Global Hub can help
We help you check that your documents are complete and consistent, explain each step and follow your application with you. Only ICA decides on a Student's Pass. We cannot guarantee approval.

## Common questions
- Can I apply before I have an offer? No. ICA requires an offer from an approved institution first.
- Can an agent submit false or "improved" documents? Never. Submitting false information to ICA is an offence and can lead to rejection.
- Where do I check my entry arrangements? Read your IPA letter and ICA's visa guidance for Bangladesh passport holders.
TXT,
                'title_bn' => 'সিঙ্গাপুর স্টুডেন্টস পাস: প্রয়োজনীয়তা ও আবেদনের ধাপ',
                'summary_bn' => 'কাদের স্টুডেন্টস পাস লাগে, সিঙ্গাপুরের ইমিগ্রেশন অ্যান্ড চেকপয়েন্টস অথরিটি (ICA) কোন কাগজপত্র চায়, কখন আবেদন করবেন এবং অনুমোদনের পর কী হয়।',
                'body_bn' => <<<'TXT'
## কাদের স্টুডেন্টস পাস লাগে
সিঙ্গাপুরের ইমিগ্রেশন অ্যান্ড চেকপয়েন্টস অথরিটি (ICA) জানায়, কোনো অনুমোদিত উচ্চশিক্ষা প্রতিষ্ঠানে (স্থানীয় বিশ্ববিদ্যালয়, পলিটেকনিক বা অনুমোদিত অফশোর ক্যাম্পাস) অথবা EduTrust সনদপ্রাপ্ত বেসরকারি শিক্ষা প্রতিষ্ঠানে পূর্ণকালীন শিক্ষার্থী হিসেবে ভর্তি হলে স্টুডেন্টস পাসের জন্য আবেদন করতে হবে। ডিপেন্ডেন্টস পাস, লং-টার্ম ভিজিট পাস বা ইমিগ্রেশন এক্সেম্পশন অর্ডারধারীদের এটি লাগে না।

## আগে প্রতিষ্ঠানের অফার
কোনো প্রতিষ্ঠান আপনাকে ভর্তির অফার দেওয়ার পরই আবেদন করা যায়। ICA-এর অনলাইন সেবায় আবেদন জমা দেওয়ার প্রয়োজনীয় তথ্য প্রতিষ্ঠানই আপনাকে দেবে।

## ICA যেসব কাগজপত্র চায়
- পাসপোর্টের বায়োডাটা পাতা এবং প্রযোজ্য হলে জন্মসনদ
- ব্যক্তিগত তথ্য: যেসব দেশে বসবাস করেছেন, শিক্ষা, চাকরি ও আর্থিক সহায়তার উৎস
- প্রযোজ্য হলে বাবা-মা, স্বামী/স্ত্রী ও/অথবা ভাইবোনের তথ্য
- সিঙ্গাপুরের আবাসিক ঠিকানা, যোগাযোগের তথ্য ও একটি ইমেইল ঠিকানা
- গত ৩ মাসের মধ্যে তোলা পাসপোর্ট সাইজের রঙিন ডিজিটাল ছবি, ICA-এর ছবির নির্দেশনা অনুযায়ী

ICA মূল কাগজপত্র এবং ইংরেজি ছাড়া অন্য ভাষার কাগজের অফিসিয়াল ইংরেজি অনুবাদ চায়। অসম্পূর্ণ কাগজপত্রের কারণে আবেদন বাতিল হতে পারে।

## কখন আবেদন করবেন
ICA কোর্স শুরুর ২ থেকে ৩ মাস আগে আবেদন করার পরামর্শ দেয়। আবেদন প্রক্রিয়াকরণের সময় আপনার সিঙ্গাপুরে থাকার প্রয়োজন নেই।

## ICA প্রকাশিত প্রক্রিয়াকরণ সময় ও ফি
ICA জানায়, উচ্চশিক্ষা প্রতিষ্ঠানের আবেদন ১ সপ্তাহের মধ্যে, আর সিঙ্গাপুরে প্রবেশে ভিসা লাগলে ২ সপ্তাহের মধ্যে প্রক্রিয়া করা হয়। বাংলাদেশ ICA-এর ভিসা-প্রয়োজনীয় দেশের তালিকায় রয়েছে। ICA আরও জানায়, কিছু আবেদনে বেশি সময় লাগতে পারে।

আমরা যখন দেখেছি, তখন ICA-এর তালিকায় ছিল অফেরতযোগ্য ৪৫ সিঙ্গাপুর ডলার প্রসেসিং ফি, ৬০ সিঙ্গাপুর ডলার ইস্যু ফি এবং প্রযোজ্য ক্ষেত্রে মাল্টিপল জার্নি ভিসার জন্য ৩০ সিঙ্গাপুর ডলার। ফি দেওয়ার আগে সবসময় ICA-এর ওয়েবসাইটে বর্তমান ফি যাচাই করুন।

## অনুমোদনের পর: IPA চিঠি
আবেদন অনুমোদিত হলে ICA একটি ইন-প্রিন্সিপাল অ্যাপ্রুভাল (IPA) চিঠি দেয়। IPA-এর মেয়াদ শেষ হওয়ার আগেই আনুষ্ঠানিকতা সম্পন্ন করতে হবে; মেয়াদ শেষ হলে আবার আবেদন করতে হয়। পরবর্তী ধাপগুলো আমাদের যাত্রাপূর্ব নির্দেশিকায় দেওয়া আছে।

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
আপনার কাগজপত্র সম্পূর্ণ ও সামঞ্জস্যপূর্ণ কি না তা যাচাইয়ে আমরা সাহায্য করি, প্রতিটি ধাপ ব্যাখ্যা করি এবং আপনার সাথে আবেদনের অগ্রগতি অনুসরণ করি। স্টুডেন্টস পাসের সিদ্ধান্ত শুধু ICA নেয়। আমরা অনুমোদনের নিশ্চয়তা দিতে পারি না।

## সাধারণ জিজ্ঞাসা
- অফার পাওয়ার আগে কি আবেদন করা যায়? না। ICA আগে কোনো অনুমোদিত প্রতিষ্ঠানের অফার চায়।
- কোনো এজেন্ট কি ভুয়া বা "সংশোধিত" কাগজ জমা দিতে পারে? কখনোই না। ICA-তে মিথ্যা তথ্য দেওয়া অপরাধ এবং এতে আবেদন বাতিল হতে পারে।
- প্রবেশের ব্যবস্থা কোথায় দেখব? আপনার IPA চিঠি এবং বাংলাদেশি পাসপোর্টধারীদের জন্য ICA-এর ভিসা নির্দেশনা দেখুন।
TXT,
                'source_urls' => [self::ICA_IHL, self::ICA_PEI, self::ICA_COLLECT, self::ICA_VISA],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'All sources fetched and checked: ICA IHL and PEI (last updated 06–09 Nov 2026), ICA completion of formalities (06 Nov 2026), ICA visa list with Bangladesh (28 Oct 2026). Owner: confirm the service description. Bengali: native-speaker review needed. Legal: immigration wording should be reviewed by a qualified person.'],
                'sort_order' => 1,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'college-admission',
                'title' => 'Singapore College Admission: Polytechnics and Private Institutions',
                'seo_title' => 'Singapore College Admission for Bangladeshi Students',
                'seo_description' => 'How international students apply to Singapore polytechnics and private colleges, how to check a private institution, and what to prepare.',
                'summary' => 'How international students apply to Singapore polytechnics and private education institutions, and how to check a private institution before you pay.',
                'body' => <<<'TXT'
## Two kinds of "college"
In Singapore, "college" usually means either a public polytechnic, which offers diplomas, or a private education institution (PEI), which offers its own or partner programmes. The rules for international students are different for each.

## Polytechnics
The Ministry of Education (MOE) explains that each polytechnic runs its own Direct Admissions Exercise for students with local or international qualifications, and that applicants with Year 12 international qualifications (such as HSC) may apply. Entry requirements, intake dates and fees are set by each polytechnic, so always use the polytechnic's own admissions page.

## Private education institutions
ICA says only EduTrust-certified private education institutions can offer places to foreign students who need a Student's Pass. Before you apply or pay any fees:
- Check the institution's registration status with the Committee for Private Education (CPE)
- Check its EduTrust certification status on the CPE website
- Ask for the full course fees, refund policy and contract in writing

## What to prepare
- Passport
- Academic certificates and transcripts (for example SSC and HSC, or O and A Level), with official English translations if needed
- English language evidence if the institution asks for it
- Any other documents the institution lists for your course

## Government tuition support
MOE's Tuition Grant can cover selected full-time diploma courses at polytechnics for eligible international students, with a service obligation after graduation. It does not apply to courses at private education institutions.

## How Eagle Global Hub can help
We help you compare courses, prepare and check your documents, and follow your application. Admission decisions are made by each institution.
TXT,
                'title_bn' => 'সিঙ্গাপুরে কলেজে ভর্তি: পলিটেকনিক ও বেসরকারি প্রতিষ্ঠান',
                'summary_bn' => 'আন্তর্জাতিক শিক্ষার্থীরা কীভাবে সিঙ্গাপুরের পলিটেকনিক ও বেসরকারি শিক্ষা প্রতিষ্ঠানে আবেদন করেন এবং টাকা দেওয়ার আগে বেসরকারি প্রতিষ্ঠান কীভাবে যাচাই করবেন।',
                'body_bn' => <<<'TXT'
## দুই ধরনের "কলেজ"
সিঙ্গাপুরে "কলেজ" বলতে সাধারণত সরকারি পলিটেকনিক (যেখানে ডিপ্লোমা দেওয়া হয়) অথবা বেসরকারি শিক্ষা প্রতিষ্ঠান (PEI) বোঝায়। আন্তর্জাতিক শিক্ষার্থীদের জন্য দুটির নিয়ম আলাদা।

## পলিটেকনিক
শিক্ষা মন্ত্রণালয় (MOE) জানায়, স্থানীয় বা আন্তর্জাতিক যোগ্যতাসম্পন্ন শিক্ষার্থীদের জন্য প্রতিটি পলিটেকনিক নিজস্ব ডাইরেক্ট অ্যাডমিশনস এক্সারসাইজ পরিচালনা করে এবং ১২ বছরের আন্তর্জাতিক যোগ্যতা (যেমন এইচএসসি) থাকলে আবেদন করা যায়। ভর্তির যোগ্যতা, ইনটেকের তারিখ ও ফি প্রতিটি পলিটেকনিক নিজে নির্ধারণ করে, তাই সবসময় সংশ্লিষ্ট পলিটেকনিকের নিজস্ব ভর্তি পেজ দেখুন।

## বেসরকারি শিক্ষা প্রতিষ্ঠান
ICA জানায়, শুধু EduTrust সনদপ্রাপ্ত বেসরকারি প্রতিষ্ঠান স্টুডেন্টস পাস প্রয়োজন এমন বিদেশি শিক্ষার্থীদের ভর্তি করতে পারে। আবেদন বা কোনো ফি দেওয়ার আগে:
- কমিটি ফর প্রাইভেট এডুকেশন (CPE)-এ প্রতিষ্ঠানের নিবন্ধন যাচাই করুন
- CPE ওয়েবসাইটে এর EduTrust সনদের অবস্থা যাচাই করুন
- সম্পূর্ণ কোর্স ফি, রিফান্ড নীতি ও চুক্তি লিখিতভাবে চেয়ে নিন

## যা প্রস্তুত রাখবেন
- পাসপোর্ট
- শিক্ষাগত সনদ ও নম্বরপত্র (যেমন এসএসসি ও এইচএসসি, বা ও এবং এ লেভেল), প্রয়োজনে অফিসিয়াল ইংরেজি অনুবাদসহ
- প্রতিষ্ঠান চাইলে ইংরেজি দক্ষতার প্রমাণ
- আপনার কোর্সের জন্য প্রতিষ্ঠানের তালিকাভুক্ত অন্যান্য কাগজ

## সরকারি টিউশন সহায়তা
MOE-এর টিউশন গ্রান্ট যোগ্য আন্তর্জাতিক শিক্ষার্থীদের জন্য পলিটেকনিকের নির্দিষ্ট পূর্ণকালীন ডিপ্লোমা কোর্সে প্রযোজ্য হতে পারে, যার সাথে স্নাতকের পর চাকরির বাধ্যবাধকতা থাকে। বেসরকারি প্রতিষ্ঠানের কোর্সে এটি প্রযোজ্য নয়।

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
কোর্স তুলনা, কাগজপত্র প্রস্তুত ও যাচাই এবং আবেদনের অগ্রগতি অনুসরণে আমরা সাহায্য করি। ভর্তির সিদ্ধান্ত প্রতিটি প্রতিষ্ঠান নিজে নেয়।
TXT,
                'source_urls' => [self::MOE_DAE, self::ICA_PEI, self::CPE_REGISTER, self::CPE_EDUTRUST, self::MOE_TG],
                'review' => ['status' => 'needs_source_verification', 'notes' => 'Verified 15 Nov 2026: ICA PEI page; MOE Direct Admissions Exercise (last updated 06 Jul 2026); MOE Tuition Grant. NOT verified: the Committee for Private Education (cpe.gov.sg) pages could not be reached from the verification environment. Owner: open both CPE links in a browser; if they work and say what the guide says, set the status to Needs owner review. Bengali: native review needed.'],
                'sort_order' => 2,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'university-admission',
                'title' => 'Singapore University Admission for International Students',
                'seo_title' => 'Singapore University Admission: A Guide for Bangladeshi Students',
                'seo_description' => 'How international students apply to Singapore universities, what to prepare, and how the Tuition Grant and its work obligation work.',
                'summary' => "How international students apply to Singapore's universities, what to prepare and what the government Tuition Grant involves.",
                'body' => <<<'TXT'
## Apply directly to the university
The Ministry of Education (MOE) directs applicants to each autonomous university's own website for admissions information. Each university publishes its own admission requirements, application periods and fees, and you apply through the university.

ICA's list of approved Institutes of Higher Learning includes the local universities, the polytechnics and approved offshore campuses of foreign universities.

## What universities commonly ask for
Every university and course is different. Check the course page for the exact list, which may include:
- Passport
- Secondary and higher secondary results (for example HSC, or A Level), with official English translations if needed
- English language test results, where required
- Personal statement, references or a portfolio, for some courses

## Tuition Grant and its obligation
MOE offers a Tuition Grant to eligible international students on selected full-time undergraduate courses at autonomous universities. Students who accept it pay subsidised fees but must serve a 3-year bond after graduation by working full-time for a Singapore entity, as MOE explains on its Tuition Grant employment page. Read the terms carefully before you accept.

## After you receive an offer
Your university will tell you how to apply for a Student's Pass. See our Student's Pass guide.

## How Eagle Global Hub can help
We help you shortlist universities and courses, prepare consistent documents and plan your timeline. Admission decisions are made only by the university.
TXT,
                'title_bn' => 'আন্তর্জাতিক শিক্ষার্থীদের জন্য সিঙ্গাপুরের বিশ্ববিদ্যালয়ে ভর্তি',
                'summary_bn' => 'আন্তর্জাতিক শিক্ষার্থীরা কীভাবে সিঙ্গাপুরের বিশ্ববিদ্যালয়ে আবেদন করেন, কী প্রস্তুত রাখবেন এবং সরকারি টিউশন গ্রান্টের শর্ত কী।',
                'body_bn' => <<<'TXT'
## সরাসরি বিশ্ববিদ্যালয়ে আবেদন
ভর্তি-সংক্রান্ত তথ্যের জন্য শিক্ষা মন্ত্রণালয় (MOE) আবেদনকারীদের প্রতিটি অটোনোমাস বিশ্ববিদ্যালয়ের নিজস্ব ওয়েবসাইটে যেতে বলে। প্রতিটি বিশ্ববিদ্যালয় নিজের ভর্তির যোগ্যতা, আবেদনের সময় ও ফি প্রকাশ করে এবং আপনি সংশ্লিষ্ট বিশ্ববিদ্যালয়ে আবেদন করেন।

ICA-এর অনুমোদিত উচ্চশিক্ষা প্রতিষ্ঠানের তালিকায় স্থানীয় বিশ্ববিদ্যালয়, পলিটেকনিক এবং বিদেশি বিশ্ববিদ্যালয়ের অনুমোদিত অফশোর ক্যাম্পাস রয়েছে।

## বিশ্ববিদ্যালয়গুলো সাধারণত যা চায়
প্রতিটি বিশ্ববিদ্যালয় ও কোর্স আলাদা। সঠিক তালিকার জন্য কোর্সের পেজ দেখুন; এতে থাকতে পারে:
- পাসপোর্ট
- মাধ্যমিক ও উচ্চমাধ্যমিকের ফল (যেমন এইচএসসি বা এ লেভেল), প্রয়োজনে অফিসিয়াল ইংরেজি অনুবাদসহ
- প্রয়োজন হলে ইংরেজি ভাষা পরীক্ষার ফল
- কিছু কোর্সে ব্যক্তিগত বিবৃতি, সুপারিশপত্র বা পোর্টফোলিও

## টিউশন গ্রান্ট ও এর বাধ্যবাধকতা
অটোনোমাস বিশ্ববিদ্যালয়ের নির্দিষ্ট পূর্ণকালীন স্নাতক কোর্সে যোগ্য আন্তর্জাতিক শিক্ষার্থীদের MOE টিউশন গ্রান্ট দেয়। যারা এটি গ্রহণ করেন তারা ভর্তুকিযুক্ত ফি দেন, কিন্তু স্নাতকের পর কোনো সিঙ্গাপুরভিত্তিক প্রতিষ্ঠানে পূর্ণকালীন চাকরির মাধ্যমে ৩ বছরের বন্ড পূরণ করতে হয়, যা MOE তাদের টিউশন গ্রান্ট চাকরি-সংক্রান্ত পেজে ব্যাখ্যা করেছে। গ্রহণের আগে শর্তগুলো মনোযোগ দিয়ে পড়ুন।

## অফার পাওয়ার পর
আপনার বিশ্ববিদ্যালয় জানাবে কীভাবে স্টুডেন্টস পাসের আবেদন করতে হবে। আমাদের স্টুডেন্টস পাস নির্দেশিকা দেখুন।

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
বিশ্ববিদ্যালয় ও কোর্স বাছাই, সামঞ্জস্যপূর্ণ কাগজপত্র প্রস্তুত এবং সময়সূচি পরিকল্পনায় আমরা সাহায্য করি। ভর্তির সিদ্ধান্ত শুধু বিশ্ববিদ্যালয় নেয়।
TXT,
                'source_urls' => [self::MOE_ADMISSIONS, self::ICA_IHL, self::MOE_TG, self::MOE_TG_EMPLOYMENT],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'All sources fetched and checked: MOE admissions (03 Jul 2026; wording corrected to match: MOE directs applicants to university websites), ICA IHL, MOE Tuition Grant overview (23 Jun 2026) and employment/bond page (23 Jun 2026). Bengali: native review needed.'],
                'sort_order' => 3,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'course-selection',
                'title' => 'Choosing a Course in Singapore: A Practical Checklist',
                'seo_title' => 'How to Choose a Course in Singapore: A Practical Checklist',
                'seo_description' => 'A practical checklist for choosing a Singapore course: institution type, recognition, total cost, intakes, Student\'s Pass eligibility and your career plans.',
                'summary' => 'A practical checklist to compare courses and institutions in Singapore before you apply.',
                'body' => <<<'TXT'
## Start with your goal
Write down what you want after graduating: further study, a job in a particular field, or returning to Bangladesh. Choose a course that leads there, not only the cheapest or fastest option.

## Check the institution type
- Universities and polytechnics are on ICA's list of approved Institutes of Higher Learning
- Private education institutions must be EduTrust-certified to enrol foreign students who need a Student's Pass. Check this on the Committee for Private Education (CPE) website

## Compare these points for every course
- Award and awarding body: who issues the certificate, and is it recognised for your next step?
- Entry requirements and English language requirements
- Intake dates and application deadlines
- Total cost: tuition, other fees and living costs for the whole course (see our study costs guide)
- Course length and whether it is full-time (a Student's Pass is for full-time study)
- Whether the course is eligible for MOE's Tuition Grant, and the obligation that comes with it

## Warning signs
- Pressure to pay before you have a written offer and fee breakdown
- Promises of guaranteed admission, a guaranteed Student's Pass or a guaranteed job
- An institution you cannot find in the official registers

## How Eagle Global Hub can help
In a consultation we look at your results, budget and goals and help you shortlist suitable options. You make the final choice, and each institution decides on admission.
TXT,
                'title_bn' => 'সিঙ্গাপুরে কোর্স বাছাই: একটি ব্যবহারিক চেকলিস্ট',
                'summary_bn' => 'আবেদনের আগে সিঙ্গাপুরের কোর্স ও প্রতিষ্ঠান তুলনা করার একটি ব্যবহারিক চেকলিস্ট।',
                'body_bn' => <<<'TXT'
## লক্ষ্য দিয়ে শুরু করুন
স্নাতকের পর আপনি কী চান তা লিখে নিন: আরও পড়াশোনা, নির্দিষ্ট ক্ষেত্রে চাকরি, নাকি বাংলাদেশে ফেরা। শুধু সবচেয়ে সস্তা বা দ্রুততম নয়, বরং সেই লক্ষ্যে পৌঁছায় এমন কোর্স বেছে নিন।

## প্রতিষ্ঠানের ধরন যাচাই করুন
- বিশ্ববিদ্যালয় ও পলিটেকনিক ICA-এর অনুমোদিত উচ্চশিক্ষা প্রতিষ্ঠানের তালিকায় আছে
- স্টুডেন্টস পাস প্রয়োজন এমন বিদেশি শিক্ষার্থী ভর্তি করতে বেসরকারি প্রতিষ্ঠানকে EduTrust সনদপ্রাপ্ত হতে হবে। কমিটি ফর প্রাইভেট এডুকেশন (CPE)-এর ওয়েবসাইটে এটি যাচাই করুন

## প্রতিটি কোর্সে এগুলো তুলনা করুন
- ডিগ্রি ও প্রদানকারী প্রতিষ্ঠান: সনদ কে দেয় এবং আপনার পরবর্তী ধাপে তা স্বীকৃত কি না
- ভর্তির যোগ্যতা ও ইংরেজি ভাষার শর্ত
- ইনটেকের তারিখ ও আবেদনের শেষ সময়
- মোট খরচ: পুরো কোর্সের টিউশন, অন্যান্য ফি ও জীবনযাত্রার খরচ (আমাদের পড়াশোনার খরচ নির্দেশিকা দেখুন)
- কোর্সের মেয়াদ এবং এটি পূর্ণকালীন কি না (স্টুডেন্টস পাস পূর্ণকালীন পড়াশোনার জন্য)
- কোর্সটি MOE টিউশন গ্রান্টের যোগ্য কি না এবং এর সাথে যুক্ত বাধ্যবাধকতা

## সতর্ক সংকেত
- লিখিত অফার ও ফির বিবরণ পাওয়ার আগেই টাকা দেওয়ার চাপ
- নিশ্চিত ভর্তি, নিশ্চিত স্টুডেন্টস পাস বা নিশ্চিত চাকরির প্রতিশ্রুতি
- এমন প্রতিষ্ঠান যা অফিসিয়াল নিবন্ধনে খুঁজে পাওয়া যায় না

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
কনসালটেশনে আমরা আপনার ফল, বাজেট ও লক্ষ্য দেখে উপযুক্ত বিকল্প বাছাইয়ে সাহায্য করি। চূড়ান্ত সিদ্ধান্ত আপনার, আর ভর্তির সিদ্ধান্ত প্রতিটি প্রতিষ্ঠান নেয়।
TXT,
                'source_urls' => [self::ICA_IHL, self::ICA_PEI, self::CPE_EDUTRUST, self::MOE_TG],
                'review' => ['status' => 'needs_source_verification', 'notes' => 'Verified 15 Nov 2026: ICA IHL and PEI pages; MOE Tuition Grant. NOT verified: CPE EduTrust page (cpe.gov.sg unreachable from the verification environment). Advice on goals, comparison and warning signs is general guidance, not official information. Bengali: native review needed.'],
                'sort_order' => 4,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'tuition-fees',
                'title' => 'Singapore Tuition Fees and Study Costs: What to Budget For',
                'seo_title' => 'Singapore Study Costs: What Bangladeshi Students Should Budget For',
                'seo_description' => 'The cost items to budget for when studying in Singapore, official Student\'s Pass fees, and how the MOE Tuition Grant and its obligation work.',
                'summary' => 'The costs to plan for when studying in Singapore, where to find official fee figures, and what the MOE Tuition Grant involves.',
                'body' => <<<'TXT'
## Why we do not publish fee figures here
Tuition fees differ by institution, course and year, and they change. The only reliable figure is the one on the institution's official fee page for your intake. Ask for a written fee breakdown before you accept an offer.

## Costs to budget for
- Tuition fees for the whole course, not only the first year
- Application and other institution fees listed by the institution
- Student's Pass fees (see below)
- Accommodation and utilities
- Food, transport, phone and study materials
- Medical costs and insurance, if your institution requires or recommends it
- Flights to Singapore and back home

## Official Student's Pass fees
When we checked ICA's website, it listed a non-refundable S$45 processing fee when you apply, an S$60 issuance fee and S$30 for a Multiple Journey Visa where applicable. Confirm the current amounts on ICA's website.

## The MOE Tuition Grant
For selected full-time diploma and undergraduate courses at polytechnics, autonomous universities, ITE and arts institutions, eligible international students can receive MOE's Tuition Grant and pay subsidised fees. It is not available for part-time courses, advanced or specialist diplomas, or courses at private education institutions. Students who accept it must serve a 3-year bond by working for a Singapore entity after graduation.

## Plan your finances honestly
Your Student's Pass application asks about your financial support, so make sure your funding plan is real and documented. Never use borrowed or temporary bank balances to look more financially secure.

## How Eagle Global Hub can help
We help you build a realistic budget from official fee pages and your own situation. We do not quote fees on behalf of institutions.
TXT,
                'title_bn' => 'সিঙ্গাপুরে টিউশন ফি ও পড়াশোনার খরচ: কী কী বাজেটে রাখবেন',
                'summary_bn' => 'সিঙ্গাপুরে পড়াশোনার জন্য যেসব খরচ পরিকল্পনা করবেন, অফিসিয়াল ফির তথ্য কোথায় পাবেন এবং MOE টিউশন গ্রান্টের শর্ত কী।',
                'body_bn' => <<<'TXT'
## এখানে কেন ফির অঙ্ক দেওয়া হয়নি
টিউশন ফি প্রতিষ্ঠান, কোর্স ও বছরভেদে আলাদা এবং তা পরিবর্তন হয়। একমাত্র নির্ভরযোগ্য অঙ্ক হলো আপনার ইনটেকের জন্য প্রতিষ্ঠানের অফিসিয়াল ফি পেজে দেওয়া অঙ্ক। অফার গ্রহণের আগে লিখিত ফির বিবরণ চেয়ে নিন।

## যেসব খরচ বাজেটে রাখবেন
- শুধু প্রথম বছর নয়, পুরো কোর্সের টিউশন ফি
- প্রতিষ্ঠানের তালিকাভুক্ত আবেদন ও অন্যান্য ফি
- স্টুডেন্টস পাসের ফি (নিচে দেখুন)
- আবাসন ও ইউটিলিটি বিল
- খাবার, যাতায়াত, ফোন ও পড়াশোনার উপকরণ
- প্রতিষ্ঠান চাইলে বা পরামর্শ দিলে চিকিৎসা খরচ ও বিমা
- সিঙ্গাপুরে যাওয়া ও দেশে ফেরার বিমান ভাড়া

## স্টুডেন্টস পাসের অফিসিয়াল ফি
আমরা যখন ICA-এর ওয়েবসাইট দেখেছি, তখন তালিকায় ছিল আবেদনের সময় অফেরতযোগ্য ৪৫ সিঙ্গাপুর ডলার প্রসেসিং ফি, ৬০ সিঙ্গাপুর ডলার ইস্যু ফি এবং প্রযোজ্য ক্ষেত্রে মাল্টিপল জার্নি ভিসার জন্য ৩০ সিঙ্গাপুর ডলার। বর্তমান অঙ্ক ICA-এর ওয়েবসাইটে যাচাই করুন।

## MOE টিউশন গ্রান্ট
পলিটেকনিক, অটোনোমাস বিশ্ববিদ্যালয়, ITE ও আর্টস প্রতিষ্ঠানের নির্দিষ্ট পূর্ণকালীন ডিপ্লোমা ও স্নাতক কোর্সে যোগ্য আন্তর্জাতিক শিক্ষার্থীরা MOE টিউশন গ্রান্ট পেয়ে ভর্তুকিযুক্ত ফি দিতে পারেন। খণ্ডকালীন কোর্স, অ্যাডভান্সড বা স্পেশালিস্ট ডিপ্লোমা এবং বেসরকারি প্রতিষ্ঠানের কোর্সে এটি পাওয়া যায় না। যারা এটি গ্রহণ করেন, স্নাতকের পর কোনো সিঙ্গাপুরভিত্তিক প্রতিষ্ঠানে কাজ করে তাদের ৩ বছরের বন্ড পূরণ করতে হয়।

## সততার সাথে আর্থিক পরিকল্পনা করুন
স্টুডেন্টস পাসের আবেদনে আপনার আর্থিক সহায়তার তথ্য চাওয়া হয়, তাই আপনার অর্থায়ন পরিকল্পনা যেন বাস্তব ও প্রমাণসহ হয়। আর্থিকভাবে বেশি সচ্ছল দেখাতে কখনো ধার করা বা সাময়িক ব্যাংক ব্যালান্স ব্যবহার করবেন না।

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
অফিসিয়াল ফি পেজ ও আপনার নিজের পরিস্থিতি থেকে বাস্তবসম্মত বাজেট তৈরিতে আমরা সাহায্য করি। আমরা প্রতিষ্ঠানের পক্ষে ফি উদ্ধৃত করি না।
TXT,
                'source_urls' => [self::ICA_IHL, self::ICA_COLLECT, self::MOE_TG, self::MOE_TG_EMPLOYMENT],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'All sources fetched and checked: ICA fees (IHL and completion pages), MOE Tuition Grant overview and bond (23 Jun 2026). No tuition figures by design. Bengali: native review needed.'],
                'sort_order' => 5,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'student-accommodation',
                'title' => 'Student Accommodation in Singapore: Options and Safe Renting',
                'seo_title' => 'Student Accommodation in Singapore: Options and Safe Renting',
                'seo_description' => 'Accommodation options for international students in Singapore, HDB rental rules for Student\'s Pass holders, and how to avoid rental scams.',
                'summary' => 'Where international students usually live in Singapore, the official HDB rules that apply to renting, and how to avoid rental scams.',
                'body' => <<<'TXT'
## Start with your institution
Many universities and polytechnics have student housing or accommodation offices. Check your institution's housing pages first: places can be limited, and application dates are usually set by the institution.

## Renting privately
Some students rent a room in a private apartment or a Housing & Development Board (HDB) flat. HDB's rules, as published on its website, include:
- Student's Pass holders are among the non-citizens who can rent an HDB flat or bedroom
- The minimum rental period is 6 months
- Flat owners need HDB approval to rent out their flat or bedrooms, and only authorised tenants may stay

Your Singapore address is also part of your Student's Pass application details, so keep it accurate and up to date.

## Avoid rental scams
- Do not pay a deposit before you have seen the room, or a trusted person has
- Ask for a written tenancy agreement and read it before signing
- Be careful with offers that are much cheaper than similar rooms nearby

## How Eagle Global Hub can help
We can explain the options and help you prepare questions for landlords and housing offices. We do not act as a property agent.
TXT,
                'title_bn' => 'সিঙ্গাপুরে শিক্ষার্থীদের আবাসন: বিকল্প ও নিরাপদে বাসা ভাড়া',
                'summary_bn' => 'সিঙ্গাপুরে আন্তর্জাতিক শিক্ষার্থীরা সাধারণত কোথায় থাকেন, ভাড়ার ক্ষেত্রে HDB-এর অফিসিয়াল নিয়ম এবং ভাড়া-সংক্রান্ত প্রতারণা কীভাবে এড়াবেন।',
                'body_bn' => <<<'TXT'
## নিজের প্রতিষ্ঠান থেকে শুরু করুন
অনেক বিশ্ববিদ্যালয় ও পলিটেকনিকে শিক্ষার্থী আবাসন বা আবাসন অফিস আছে। আগে আপনার প্রতিষ্ঠানের আবাসন পেজ দেখুন: আসন সীমিত হতে পারে এবং আবেদনের তারিখ সাধারণত প্রতিষ্ঠান ঠিক করে।

## ব্যক্তিগতভাবে ভাড়া
কিছু শিক্ষার্থী বেসরকারি অ্যাপার্টমেন্ট বা হাউজিং অ্যান্ড ডেভেলপমেন্ট বোর্ড (HDB) ফ্ল্যাটে রুম ভাড়া নেন। HDB-এর ওয়েবসাইটে প্রকাশিত নিয়মের মধ্যে রয়েছে:
- স্টুডেন্টস পাসধারীরা HDB ফ্ল্যাট বা বেডরুম ভাড়া নিতে পারেন এমন অনাগরিকদের মধ্যে পড়েন
- ন্যূনতম ভাড়ার মেয়াদ ৬ মাস
- ফ্ল্যাট বা বেডরুম ভাড়া দিতে মালিকের HDB-এর অনুমোদন লাগে এবং শুধু অনুমোদিত ভাড়াটিয়ারা থাকতে পারেন

আপনার সিঙ্গাপুরের ঠিকানা স্টুডেন্টস পাসের আবেদনের তথ্যের অংশ, তাই এটি সঠিক ও হালনাগাদ রাখুন।

## ভাড়া-সংক্রান্ত প্রতারণা এড়িয়ে চলুন
- আপনি বা বিশ্বস্ত কেউ রুম না দেখা পর্যন্ত জামানত দেবেন না
- লিখিত ভাড়ার চুক্তি চেয়ে নিন এবং সই করার আগে পড়ুন
- আশেপাশের একই রকম রুমের চেয়ে অনেক সস্তা অফারে সতর্ক থাকুন

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
আমরা বিকল্পগুলো ব্যাখ্যা করতে এবং বাড়িওয়ালা ও আবাসন অফিসকে কী জিজ্ঞাসা করবেন তা প্রস্তুত করতে সাহায্য করতে পারি। আমরা প্রপার্টি এজেন্ট হিসেবে কাজ করি না।
TXT,
                'source_urls' => [self::HDB_RENTING, self::ICA_IHL],
                'review' => ['status' => 'needs_source_verification', 'notes' => 'NOT verified: HDB pages load their rules dynamically and the eligibility, 6-month minimum and owner-approval statements could not be read on 15 Nov 2026. Keep unpublished until a person confirms them on hdb.gov.sg, or remove that section. Owner: confirm "we do not act as a property agent". Bengali: native review needed.'],
                'sort_order' => 6,
            ],
            [
                'type' => ContentType::Guide,
                'slug' => 'pre-departure',
                'title' => 'Before You Fly: Singapore Pre-Departure and Arrival Checklist',
                'seo_title' => 'Singapore Pre-Departure Checklist for Students from Bangladesh',
                'seo_description' => 'What to do after your In-Principle Approval: when to travel, the SG Arrival Card, completing Student\'s Pass formalities and receiving your digital pass.',
                'summary' => "What to do between your In-Principle Approval (IPA) and receiving your Student's Pass: travel timing, arrival and completing formalities.",
                'body' => <<<'TXT'
## Before you travel
- Keep your In-Principle Approval (IPA) letter, passport and original documents together
- Check the IPA's validity: ICA says all formalities must be completed before it expires
- ICA says to arrive no more than 1 month before your course starts
- Submit the SG Arrival Card through ICA's official e-service within 3 days before you arrive (the 3 days include your arrival day). ICA says submitting it is free

## At immigration
Show your IPA letter at the checkpoint. ICA notes that the immigration officer decides whether to grant you a Visit Pass, so an IPA is not a guarantee of entry. Check the expiry date of your Visit Pass and apply for an extension if you cannot finish formalities in time.

## Completing formalities
- Log in to ICA's e-service and complete the required tasks before your IPA expires
- Acknowledge the Student's Pass terms and conditions
- Complete a medical examination if ICA says it applies to you
- Attend your enrolment appointment for biometrics, either one your school arranges or an e-Appointment you book with ICA
- Pay the issuance fee and any Multiple Journey Visa fee by the deadline

## Receiving your Student's Pass
ICA issues the Student's Pass digitally. You will receive an email with instructions to retrieve it through FileSG, and you can also access it through the Singpass app.

## How Eagle Global Hub can help
We help you prepare a document checklist, plan your travel dates around your IPA and course start, and arrange your air ticket if you wish.
TXT,
                'title_bn' => 'যাত্রার আগে: সিঙ্গাপুরে যাত্রাপূর্ব ও পৌঁছানোর চেকলিস্ট',
                'summary_bn' => 'ইন-প্রিন্সিপাল অ্যাপ্রুভাল (IPA) পাওয়া থেকে স্টুডেন্টস পাস হাতে পাওয়া পর্যন্ত যা করবেন: যাত্রার সময়, পৌঁছানো ও আনুষ্ঠানিকতা সম্পন্ন করা।',
                'body_bn' => <<<'TXT'
## যাত্রার আগে
- আপনার ইন-প্রিন্সিপাল অ্যাপ্রুভাল (IPA) চিঠি, পাসপোর্ট ও মূল কাগজপত্র একসাথে রাখুন
- IPA-এর মেয়াদ দেখুন: ICA জানায়, মেয়াদ শেষ হওয়ার আগেই সব আনুষ্ঠানিকতা শেষ করতে হবে
- ICA অনুযায়ী কোর্স শুরুর ১ মাসের বেশি আগে পৌঁছাবেন না
- পৌঁছানোর আগের ৩ দিনের মধ্যে (পৌঁছানোর দিনসহ) ICA-এর অফিসিয়াল ই-সেবায় এসজি অ্যারাইভাল কার্ড জমা দিন। ICA জানায়, এটি জমা দেওয়া বিনামূল্যে

## ইমিগ্রেশনে
চেকপয়েন্টে আপনার IPA চিঠি দেখান। ICA জানায়, ভিজিট পাস দেওয়া হবে কি না তা ইমিগ্রেশন কর্মকর্তা ঠিক করেন, তাই IPA প্রবেশের নিশ্চয়তা নয়। ভিজিট পাসের মেয়াদ দেখুন এবং সময়মতো আনুষ্ঠানিকতা শেষ করতে না পারলে মেয়াদ বাড়ানোর আবেদন করুন।

## আনুষ্ঠানিকতা সম্পন্ন করা
- ICA-এর ই-সেবায় লগইন করে IPA-এর মেয়াদ শেষ হওয়ার আগে প্রয়োজনীয় কাজ শেষ করুন
- স্টুডেন্টস পাসের শর্তাবলি স্বীকার করুন
- ICA জানালে মেডিকেল পরীক্ষা সম্পন্ন করুন
- বায়োমেট্রিকের জন্য এনরোলমেন্ট অ্যাপয়েন্টমেন্টে উপস্থিত হোন: আপনার প্রতিষ্ঠান আয়োজন করলে সেটি, নইলে ICA-তে ই-অ্যাপয়েন্টমেন্ট বুক করুন
- নির্ধারিত সময়ের মধ্যে ইস্যু ফি এবং প্রযোজ্য হলে মাল্টিপল জার্নি ভিসার ফি পরিশোধ করুন

## স্টুডেন্টস পাস হাতে পাওয়া
ICA স্টুডেন্টস পাস ডিজিটালি ইস্যু করে। FileSG থেকে তা সংগ্রহের নির্দেশনাসহ আপনি একটি ইমেইল পাবেন এবং সিংপাস অ্যাপেও এটি দেখা যাবে।

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
কাগজপত্রের চেকলিস্ট প্রস্তুত, IPA ও কোর্স শুরুর তারিখ অনুযায়ী যাত্রার পরিকল্পনা এবং আপনি চাইলে এয়ার টিকিটের ব্যবস্থা করতে আমরা সাহায্য করি।
TXT,
                'source_urls' => [self::ICA_COLLECT, self::ICA_SGAC, self::ICA_VISA],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'All sources fetched and checked: ICA completion of formalities (06 Nov 2026), SG Arrival Card (28 Oct 2026; wording corrected to match), ICA visa list (28 Oct 2026). Owner: confirm the air-ticket service mention. Bengali: native review needed.'],
                'sort_order' => 7,
            ],
        ];
    }

    /**
     * Proposed company statements. Page sections are only shown once an
     * owner approves and publishes them.
     *
     * @return list<array<string, mixed>>
     */
    private function companyStatements(): array
    {
        $proposed = ['status' => 'needs_owner_review', 'notes' => 'PROPOSED business statement written for owner review. It makes no claims about results, licences or partnerships. Edit freely, then approve and publish. Bengali: native-speaker review needed.'];

        return [
            [
                'type' => ContentType::PageSection,
                'slug' => 'mission',
                'title' => 'Our Mission',
                'body' => 'To give students and travellers from Bangladesh honest, clear and well-organised support, from choosing a course in Singapore to planning a journey, so they can make informed decisions and complete each step with confidence.',
                'title_bn' => 'আমাদের মিশন',
                'body_bn' => 'বাংলাদেশের শিক্ষার্থী ও ভ্রমণকারীদের সৎ, স্পষ্ট ও সুসংগঠিত সহায়তা দেওয়া — সিঙ্গাপুরে কোর্স বাছাই থেকে ভ্রমণ পরিকল্পনা পর্যন্ত — যাতে তারা জেনে-বুঝে সিদ্ধান্ত নিতে এবং আত্মবিশ্বাসের সাথে প্রতিটি ধাপ সম্পন্ন করতে পারেন।',
                'review' => $proposed,
            ],
            [
                'type' => ContentType::PageSection,
                'slug' => 'vision',
                'title' => 'Our Vision',
                'body' => 'To be a trusted name for Singapore study consultancy and travel services in Bangladesh, known for transparent advice, careful document preparation and dependable customer care.',
                'title_bn' => 'আমাদের ভিশন',
                'body_bn' => 'স্বচ্ছ পরামর্শ, যত্নশীল কাগজপত্র প্রস্তুতি ও নির্ভরযোগ্য গ্রাহকসেবার জন্য বাংলাদেশে সিঙ্গাপুর স্টাডি কনসালটেন্সি ও ভ্রমণ সেবায় একটি বিশ্বস্ত নাম হয়ে ওঠা।',
                'review' => $proposed,
            ],
            [
                'type' => ContentType::PageSection,
                'slug' => 'why-different',
                'title' => 'What makes us different',
                'body' => <<<'TXT'
- Honest advice: we explain what institutions and Singapore's authorities require, and we never promise admission or a Student's Pass
- One consultant follows your file, and you can see your progress, documents and messages in your own student portal
- Your documents are stored privately and seen only by you and authorised staff
- Clear costs: we point you to official fee pages and explain every payment we receive in writing
- Support beyond admission: air tickets, accommodation guidance and pre-departure preparation
TXT,
                'title_bn' => 'কেন আমরা ব্যতিক্রম?',
                'body_bn' => <<<'TXT'
- সৎ পরামর্শ: প্রতিষ্ঠান ও সিঙ্গাপুরের কর্তৃপক্ষ কী চায় তা আমরা ব্যাখ্যা করি, আর কখনো ভর্তি বা স্টুডেন্টস পাসের প্রতিশ্রুতি দিই না
- একজন কনসালটেন্ট আপনার ফাইল দেখাশোনা করেন, আর আপনি নিজের স্টুডেন্ট পোর্টালে অগ্রগতি, কাগজপত্র ও বার্তা দেখতে পারেন
- আপনার কাগজপত্র গোপনে সংরক্ষিত থাকে এবং শুধু আপনি ও অনুমোদিত কর্মীরা দেখতে পারেন
- স্পষ্ট খরচ: আমরা অফিসিয়াল ফি পেজ দেখিয়ে দিই এবং আমাদের গ্রহণ করা প্রতিটি পেমেন্ট লিখিতভাবে ব্যাখ্যা করি
- ভর্তির পরও সহায়তা: এয়ার টিকিট, আবাসন বিষয়ক নির্দেশনা ও যাত্রাপূর্ব প্রস্তুতি
TXT,
                'review' => ['status' => 'needs_owner_review', 'notes' => 'PROPOSED. Every point describes a feature that exists in this system (assigned consultant, student portal, private documents, written payment records). Owner: confirm each matches how the business actually works. Bengali: native-speaker review needed.'],
            ],
            [
                'type' => ContentType::CompanyProfile,
                'slug' => 'eagle-global-hub-ltd',
                'title' => 'Eagle Global Hub LTD',
                'summary' => 'A Dhaka-based travel and visa agency whose main focus is full student consultancy for Singapore.',
                'body' => <<<'TXT'
Eagle Global Hub LTD is a travel and visa agency based in Gulshan, Dhaka. Our main focus is full student consultancy for students who want to study in Singapore, from choosing a course to preparing for departure.

## Services
- Student visa and study consultancy, with Singapore as our main destination
- Tourist, business and work visa assistance
- Air tickets, hotel bookings and tour packages
- Ground transport arrangements: trains, buses, cars and bikes

## Office
Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212
TXT,
                'title_bn' => 'ঈগল গ্লোবাল হাব লিমিটেড',
                'summary_bn' => 'ঢাকাভিত্তিক একটি ট্রাভেল ও ভিসা এজেন্সি, যার প্রধান মনোযোগ সিঙ্গাপুরের জন্য পূর্ণাঙ্গ স্টুডেন্ট কনসালটেন্সি।',
                'body_bn' => <<<'TXT'
ঈগল গ্লোবাল হাব লিমিটেড ঢাকার গুলশানভিত্তিক একটি ট্রাভেল ও ভিসা এজেন্সি। যারা সিঙ্গাপুরে পড়তে চান, কোর্স বাছাই থেকে যাত্রার প্রস্তুতি পর্যন্ত তাদের পূর্ণাঙ্গ স্টুডেন্ট কনসালটেন্সি আমাদের প্রধান মনোযোগ।

## সেবাসমূহ
- স্টুডেন্ট ভিসা ও স্টাডি কনসালটেন্সি, যেখানে সিঙ্গাপুর আমাদের প্রধান গন্তব্য
- ট্যুরিস্ট, বিজনেস ও ওয়ার্ক ভিসা সহায়তা
- এয়ার টিকিট, হোটেল বুকিং ও ট্যুর প্যাকেজ
- স্থল যাতায়াতের ব্যবস্থা: ট্রেন, বাস, গাড়ি ও বাইক

## অফিস
মাইশা চৌধুরী টাওয়ার, গ-৩০/বি, প্রগতি সরণি, শাহজাদপুর, গুলশান, ঢাকা-১২১২
TXT,
                'location' => 'Mysha Chowdhury Tower, Ga-30/B, Pragati Sharani, Shahjadpur, Gulshan, Dhaka-1212',
                'review' => ['status' => 'needs_owner_review', 'notes' => "Uses only information already on the website (name, office address) and the PDF's service list. OWNER TO PROVIDE before publishing: year established, trade licence number, other registrations or licences (e.g. travel agency registration), certificates (upload to the Media Library), history and achievements with evidence. Super-admin approval is required to publish."],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function businessServices(): array
    {
        return [
            [
                'type' => ContentType::BusinessService,
                'slug' => 'export-import',
                'category' => 'export-import',
                'title' => 'Export & Import Support',
                'summary' => 'Enquiry-based support for businesses with export and import needs. Tell us what you need and our team will reply.',
                'body' => <<<'TXT'
Eagle Global Hub LTD accepts enquiries from businesses that need help with export or import work. Send us your requirements and our team will contact you to discuss what we can arrange.

## What to include in your enquiry
- Whether you are importing or exporting
- The type of goods
- Origin and destination countries
- Approximate quantity and timeline

We will tell you clearly which parts we can handle ourselves and which need a licensed partner or the relevant authorities.
TXT,
                'title_bn' => 'এক্সপোর্ট ও ইমপোর্ট সহায়তা',
                'summary_bn' => 'এক্সপোর্ট ও ইমপোর্ট প্রয়োজন আছে এমন প্রতিষ্ঠানের জন্য অনুসন্ধানভিত্তিক সহায়তা। আপনার প্রয়োজন জানান, আমাদের টিম উত্তর দেবে।',
                'body_bn' => <<<'TXT'
এক্সপোর্ট বা ইমপোর্টের কাজে সহায়তা প্রয়োজন এমন প্রতিষ্ঠানের অনুসন্ধান ঈগল গ্লোবাল হাব লিমিটেড গ্রহণ করে। আপনার প্রয়োজন জানান; আমরা কী ব্যবস্থা করতে পারি তা আলোচনার জন্য আমাদের টিম যোগাযোগ করবে।

## অনুসন্ধানে যা লিখবেন
- আপনি ইমপোর্ট করবেন নাকি এক্সপোর্ট
- পণ্যের ধরন
- উৎস ও গন্তব্য দেশ
- আনুমানিক পরিমাণ ও সময়সীমা

কোন অংশ আমরা নিজেরা করতে পারি এবং কোন অংশে লাইসেন্সপ্রাপ্ত পার্টনার বা সংশ্লিষ্ট কর্তৃপক্ষ প্রয়োজন, তা আমরা স্পষ্টভাবে জানাব।
TXT,
                'review' => ['status' => 'needs_owner_review', 'notes' => 'General description only. OWNER TO PROVIDE: the actual scope of export/import services; any licences (e.g. IRC/ERC) — do not mention licences until verified. The enquiry form /enquiries/export-import already works.'],
            ],
            [
                'type' => ContentType::BusinessService,
                'slug' => 'civil-construction',
                'category' => 'civil-construction',
                'title' => 'Civil & Construction Work (Land Purpose Work)',
                'summary' => 'Enquiry-based support for civil construction and land-related work. Tell us about your project and our team will reply.',
                'body' => <<<'TXT'
Eagle Global Hub LTD accepts enquiries about civil construction and land-related work. Describe your project and our team will contact you to discuss it.

## What to include in your enquiry
- Type of work: civil construction, land-related work or other
- Project location
- Approximate size and timeline
- Any drawings or approvals you already have

We will explain clearly what we can arrange and what requires licensed engineers, contractors or government approvals.
TXT,
                'title_bn' => 'সিভিল ও কনস্ট্রাকশন কাজ (জমি-সংক্রান্ত কাজ)',
                'summary_bn' => 'সিভিল কনস্ট্রাকশন ও জমি-সংক্রান্ত কাজের জন্য অনুসন্ধানভিত্তিক সহায়তা। আপনার প্রকল্প সম্পর্কে জানান, আমাদের টিম উত্তর দেবে।',
                'body_bn' => <<<'TXT'
সিভিল কনস্ট্রাকশন ও জমি-সংক্রান্ত কাজের অনুসন্ধান ঈগল গ্লোবাল হাব লিমিটেড গ্রহণ করে। আপনার প্রকল্পের বিবরণ দিন; আলোচনার জন্য আমাদের টিম যোগাযোগ করবে।

## অনুসন্ধানে যা লিখবেন
- কাজের ধরন: সিভিল কনস্ট্রাকশন, জমি-সংক্রান্ত কাজ বা অন্যান্য
- প্রকল্পের অবস্থান
- আনুমানিক আকার ও সময়সীমা
- আপনার কাছে থাকা নকশা বা অনুমোদন

আমরা কী ব্যবস্থা করতে পারি এবং কোন কাজে লাইসেন্সপ্রাপ্ত প্রকৌশলী, ঠিকাদার বা সরকারি অনুমোদন প্রয়োজন, তা স্পষ্টভাবে জানাব।
TXT,
                'review' => ['status' => 'needs_owner_review', 'notes' => 'General description only. No projects are listed. OWNER TO PROVIDE: actual scope, qualifications or licences, and real completed projects with photos (add as Construction Project entries; each needs owner approval).'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function faqs(): array
    {
        $review = ['status' => 'needs_owner_review', 'notes' => 'Describes how this website and the official process work. Owner: confirm wording. Bengali: native-speaker review needed.'];

        return array_map(fn (array $faq): array => ['type' => ContentType::Faq, 'summary' => null, 'review' => $review, 'sort_order' => 0, ...$faq], [
            ['slug' => 'guarantee', 'title' => "Can you guarantee admission or a Student's Pass?", 'body' => "No. Admission decisions are made by each institution, and Student's Pass decisions by Singapore's Immigration & Checkpoints Authority (ICA). We help you prepare a complete, honest application and follow it with you.", 'title_bn' => 'আপনারা কি ভর্তি বা স্টুডেন্টস পাসের নিশ্চয়তা দিতে পারেন?', 'body_bn' => 'না। ভর্তির সিদ্ধান্ত প্রতিটি প্রতিষ্ঠান নেয়, আর স্টুডেন্টস পাসের সিদ্ধান্ত নেয় সিঙ্গাপুরের ইমিগ্রেশন অ্যান্ড চেকপয়েন্টস অথরিটি (ICA)। আমরা সম্পূর্ণ ও সৎ আবেদন প্রস্তুত করতে এবং তা অনুসরণ করতে আপনাকে সাহায্য করি।'],
            ['slug' => 'track-application', 'title' => 'How can I follow my application?', 'body' => 'Once your consultant opens your file, sign in with the email address you gave us and open "My Applications". You can see your status, timeline, documents and messages there.', 'title_bn' => 'আমি কীভাবে আমার আবেদনের অগ্রগতি দেখব?', 'body_bn' => 'আপনার কনসালটেন্ট ফাইল খোলার পর, আমাদের দেওয়া ইমেইল দিয়ে সাইন ইন করে "আমার আবেদনসমূহ" খুলুন। সেখানে অবস্থা, টাইমলাইন, কাগজপত্র ও বার্তা দেখতে পাবেন।'],
            ['slug' => 'document-privacy', 'title' => 'Who can see the documents I upload?', 'body' => 'Only you and authorised Eagle Global Hub staff working on your file. Documents are stored privately and are never published on the website.', 'title_bn' => 'আমার আপলোড করা কাগজপত্র কে দেখতে পারে?', 'body_bn' => 'শুধু আপনি এবং আপনার ফাইলে কাজ করা ঈগল গ্লোবাল হাবের অনুমোদিত কর্মীরা। কাগজপত্র গোপনে সংরক্ষিত থাকে এবং কখনো ওয়েবসাইটে প্রকাশ করা হয় না।'],
            ['slug' => 'payments', 'title' => 'Can I pay online?', 'body' => 'Not at the moment. Payments are made at our office or by bank or mobile banking transfer, and appear in your portal after our accounts team verifies them. Always ask for a written receipt.', 'title_bn' => 'আমি কি অনলাইনে পেমেন্ট করতে পারি?', 'body_bn' => 'এই মুহূর্তে নয়। পেমেন্ট আমাদের অফিসে অথবা ব্যাংক বা মোবাইল ব্যাংকিং ট্রান্সফারের মাধ্যমে করা হয় এবং আমাদের অ্যাকাউন্টস টিম যাচাইয়ের পর আপনার পোর্টালে দেখা যায়। সবসময় লিখিত রসিদ চেয়ে নিন।'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function inspiration(): array
    {
        return [
            [
                'type' => ContentType::Inspiration,
                'slug' => 'first-trip-to-coxs-bazar',
                'category' => 'Bangladesh',
                'title' => "Planning a First Trip to Cox's Bazar",
                'summary' => "What to know before visiting Cox's Bazar, home of Bangladesh's long sandy sea beach.",
                'body' => <<<'TXT'
Cox's Bazar is known for its sea beach. The Bangladesh Parjatan Corporation describes it as a continuous sandy stretch of about 120 km, from Cox's Bazar town to Badarmokam, and the Bangladesh Tourism Board calls it the longest beach in the world.

## Planning tips
- Choose your travel dates early if you are visiting during holidays, when demand is high
- Follow local safety instructions and flags on the beach, and swim only where it is allowed
- Keep copies of your ID and booking details on your phone and on paper

## How Eagle Global Hub can help
Send us an enquiry with your dates and number of travellers, and our team will reply with transport and hotel options.
TXT,
                'title_bn' => 'কক্সবাজারে প্রথম ভ্রমণের পরিকল্পনা',
                'summary_bn' => 'বাংলাদেশের দীর্ঘ বালুকাময় সমুদ্রসৈকতের শহর কক্সবাজারে যাওয়ার আগে যা জানা দরকার।',
                'body_bn' => <<<'TXT'
কক্সবাজার তার সমুদ্রসৈকতের জন্য পরিচিত। বাংলাদেশ পর্যটন করপোরেশনের বর্ণনা অনুযায়ী এটি কক্সবাজার শহর থেকে বদরমোকাম পর্যন্ত প্রায় ১২০ কিলোমিটার দীর্ঘ একটানা বালুকাময় সৈকত, আর বাংলাদেশ ট্যুরিজম বোর্ড একে বিশ্বের দীর্ঘতম সৈকত বলে উল্লেখ করে।

## পরিকল্পনার টিপস
- ছুটির সময়ে চাহিদা বেশি থাকে, তাই আগে থেকে যাত্রার তারিখ ঠিক করুন
- সৈকতে স্থানীয় নিরাপত্তা নির্দেশনা ও পতাকা মেনে চলুন এবং শুধু অনুমোদিত স্থানে সাঁতার কাটুন
- পরিচয়পত্র ও বুকিংয়ের তথ্যের কপি ফোনে ও কাগজে রাখুন

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
আপনার তারিখ ও যাত্রীর সংখ্যা জানিয়ে অনুসন্ধান পাঠান; আমাদের টিম যাতায়াত ও হোটেলের বিকল্পসহ উত্তর দেবে।
TXT,
                'source_urls' => ['https://parjatan.gov.bd/pages/static-pages/6922dc9a933eb65569e11366', 'https://tourismboard.gov.bd/pages/static-pages/6922dc8d933eb65569e10f2f'],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'Facts from Bangladesh Parjatan Corporation and Bangladesh Tourism Board pages (search results, 15 Nov 2026). Add an owner-provided or licensed photo. Bengali: native-speaker review needed.'],
            ],
            [
                'type' => ContentType::Inspiration,
                'slug' => 'first-visit-to-sylhet',
                'category' => 'Bangladesh',
                'title' => 'A First Visit to Sylhet',
                'summary' => 'Ideas for a first visit to Sylhet: the shrine of Hazrat Shahjalal, Ratargul swamp forest and Jaflong.',
                'body' => <<<'TXT'
Sylhet in north-east Bangladesh is known for its green hills, tea gardens and rivers. Places described on the Sylhet district's official tourism pages include:

- The shrine of Hazrat Shahjalal, on a hill in Sylhet city
- Ratargul, a freshwater swamp forest in Gowainghat, usually explored by boat
- Jaflong in Gowainghat, near the border, known for its scenery and stone-filled river

## Planning tips
- Travel times to Gowainghat depend on road conditions, so plan day trips with extra time
- Respect local customs when visiting religious sites, and dress modestly

## How Eagle Global Hub can help
Send us an enquiry with your dates and number of travellers, and our team will reply with transport and hotel options.
TXT,
                'title_bn' => 'সিলেটে প্রথম ভ্রমণ',
                'summary_bn' => 'সিলেটে প্রথম ভ্রমণের ধারণা: হযরত শাহজালাল (রহ.)-এর মাজার, রাতারগুল সোয়াম্প ফরেস্ট ও জাফলং।',
                'body_bn' => <<<'TXT'
উত্তর-পূর্ব বাংলাদেশের সিলেট সবুজ পাহাড়, চা-বাগান ও নদীর জন্য পরিচিত। সিলেট জেলার অফিসিয়াল পর্যটন পেজে বর্ণিত স্থানের মধ্যে রয়েছে:

- সিলেট শহরের একটি টিলার ওপর হযরত শাহজালাল (রহ.)-এর মাজার
- গোয়াইনঘাটের রাতারগুল, একটি মিঠাপানির জলাবন, যা সাধারণত নৌকায় ঘুরে দেখা হয়
- সীমান্তের কাছে গোয়াইনঘাটের জাফলং, যা প্রাকৃতিক দৃশ্য ও পাথরভরা নদীর জন্য পরিচিত

## পরিকল্পনার টিপস
- গোয়াইনঘাটে যাতায়াতের সময় রাস্তার অবস্থার ওপর নির্ভর করে, তাই দিনের ভ্রমণে হাতে বাড়তি সময় রাখুন
- ধর্মীয় স্থানে স্থানীয় রীতিনীতি মেনে চলুন এবং শালীন পোশাক পরুন

## ঈগল গ্লোবাল হাব যেভাবে সাহায্য করতে পারে
আপনার তারিখ ও যাত্রীর সংখ্যা জানিয়ে অনুসন্ধান পাঠান; আমাদের টিম যাতায়াত ও হোটেলের বিকল্পসহ উত্তর দেবে।
TXT,
                'source_urls' => ['https://sylhet.gov.bd/pages/tourist-spot', 'https://gowainghat.sylhet.gov.bd/pages/tourist-spot/696ca15383f6c1a51f3ab5fb'],
                'review' => ['status' => 'needs_owner_review', 'verified' => self::CHECKED_ON, 'notes' => 'Facts from Sylhet district government tourism pages (search results, 15 Nov 2026). Add an owner-provided or licensed photo. Bengali: native-speaker review needed.'],
            ],
        ];
    }

    /**
     * Fills empty, unpublished Explore Bangladesh destinations with
     * source-based descriptions. Published or edited ones are left alone.
     */
    private function destinations(): void
    {
        $texts = [
            'coxs-bazar' => [
                'region' => 'Chattogram Division',
                'summary' => "Bangladesh's long sandy sea beach, described by the Bangladesh Parjatan Corporation as a continuous stretch of about 120 km.",
                'description' => "Cox's Bazar is a coastal town in south-east Bangladesh, known for its sea beach. The Bangladesh Parjatan Corporation describes a continuous sandy beach of about 120 km from Cox's Bazar town to Badarmokam, and the Bangladesh Tourism Board calls it the longest beach in the world.\n\nAsk our team about transport and hotel options for your dates.\n\nSources: Bangladesh Parjatan Corporation; Bangladesh Tourism Board.",
            ],
            'sylhet' => [
                'region' => 'Sylhet Division',
                'summary' => 'Green hills, tea gardens and rivers, with places such as Ratargul swamp forest and Jaflong nearby.',
                'description' => "Sylhet in north-east Bangladesh is known for its hills, tea gardens and rivers. The Sylhet district's official tourism pages describe places such as the shrine of Hazrat Shahjalal in Sylhet city, Ratargul freshwater swamp forest and Jaflong in Gowainghat.\n\nAsk our team about transport and hotel options for your dates.\n\nSources: Sylhet district administration tourism pages.",
            ],
            'kuakata' => [
                'region' => 'Barishal Division',
                'summary' => 'A sea beach in southern Bangladesh where visitors can watch both sunrise and sunset over the sea.',
                'description' => "Kuakata is a sea beach in southern Bangladesh. The Bangladesh Tourism Board describes it as a beach where both the sunrise and the sunset can be watched.\n\nAsk our team about transport and hotel options for your dates.\n\nSources: Bangladesh Tourism Board; Bangladesh Parjatan Corporation.",
            ],
        ];

        foreach ($texts as $slug => $text) {
            $destination = Destination::query()->where('slug', $slug)->first();
            if ($destination === null || $destination->is_published || trim((string) $destination->description) !== '') {
                continue;
            }

            $destination->forceFill([
                'region' => $destination->region ?: $text['region'],
                'summary' => $destination->summary ?: $text['summary'],
                'description' => $text['description'],
            ])->save();
        }
    }

    /**
     * Proposed forum categories, created closed. Moderators open them after
     * agreeing the moderation guidelines (docs/CONTENT_REVIEW_CHECKLIST.md).
     */
    private function forumCategories(): void
    {
        $categories = [
            ['slug' => 'study-in-singapore', 'name' => 'Studying in Singapore', 'description' => 'Questions about courses, institutions and student life in Singapore.', 'visibility' => 'public'],
            ['slug' => 'students-pass-documents', 'name' => "Student's Pass & documents", 'description' => "General questions about the Student's Pass process. Never post passport numbers or personal documents.", 'visibility' => 'members'],
            ['slug' => 'travel-in-bangladesh', 'name' => 'Travel in Bangladesh', 'description' => "Tips and questions about travelling to places such as Cox's Bazar, Sylhet and Kuakata.", 'visibility' => 'public'],
            ['slug' => 'general-questions', 'name' => 'General questions', 'description' => 'Anything else about our services.', 'visibility' => 'public'],
        ];

        foreach ($categories as $position => $category) {
            ForumCategory::query()->firstOrCreate(['slug' => $category['slug']], [
                ...$category,
                'requires_approval' => true,
                'is_active' => false,
                'sort_order' => $position + 1,
            ]);
        }
    }

    /**
     * A proposed rewards framework, unpublished and without earning rules, so
     * it cannot be published until the owner writes them.
     */
    private function rewardsFramework(): void
    {
        RewardProgram::query()->firstOrCreate(['slug' => 'eagle-rewards'], [
            'name' => 'Eagle Rewards (proposed)',
            'description' => 'PROPOSED loyalty programme for owner review. Not active.',
            'terms' => <<<'TXT'
PROPOSED TERMS — FOR OWNER AND LEGAL REVIEW. NOT IN EFFECT.

1. Membership: available to customers with a verified Eagle Global Hub account.
2. Points have no cash value and cannot be exchanged for cash. [OWNER TO DECIDE what points can be redeemed for.]
3. Points are recorded by Eagle Global Hub staff only, and appear in your account under Rewards.
4. Points expire [OWNER TO DECIDE: number of days] after they are added.
5. Eagle Global Hub may correct points recorded in error and may change or end the programme with [OWNER TO DECIDE: notice period] notice.
6. Points never affect admission, visa or Student's Pass decisions, which are made only by institutions and authorities.
TXT,
            'earning_rules' => null,
            'is_published' => false,
        ]);
    }
}
