@extends('layouts.site')

@section('title', __('Study in Malaysia from Bangladesh'))
@section('meta_description', __('Study in Malaysia from Bangladesh: universities and colleges, the EMGS Student Pass and eVAL step by step, documents, medical screening, living costs in MYR and a free consultation.'))
@section('body_class', 'sg-body-v2')

@push('head')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Service', 'name' => 'Study in Malaysia consultancy', 'serviceType' => 'Student consultancy', 'areaServed' => 'Bangladesh', 'url' => route('consultancy.malaysia'), 'provider' => ['@type' => 'EducationalOrganization', 'name' => $siteName ?? 'Eagle Global Hub LTD', 'url' => route('home')]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@php
    $levels = \App\Models\StudentEnquiry::STUDY_LEVELS;
    $courseLevels = collect(['diploma', 'foundation', 'bachelor', 'master'])->merge($courses->pluck('study_level')->filter())->unique()->values();
    // Official sources (EMGS, Education Malaysia Global Services). Every rule below comes from these pages.
    $sources = [
        'guidelines' => ['url' => 'https://visa.educationmalaysia.gov.my/guidelines', 'label' => __('EMGS guidelines and required documents')],
        'steps' => ['url' => 'https://educationmalaysia.gov.my/blogs/study-in-malaysia-step-by-step-how-to-apply-for-a-student-visa', 'label' => __('EMGS: how to apply for a student visa, step by step')],
        'faq' => ['url' => 'https://educationmalaysia.gov.my/get-in-touch/faq', 'label' => __('EMGS frequently asked questions')],
        'medical' => ['url' => 'https://visa.educationmalaysia.gov.my/guidelines/medical-screening', 'label' => __('EMGS post-arrival medical screening')],
        'fees' => ['url' => 'https://visa.educationmalaysia.gov.my/student-visa/calculate-application-fees', 'label' => __('EMGS fee calculator')],
    ];
    $documents = [
        __('Passport: the data page and every page with a visa. EMGS asks for a passport valid for at least 18 months.'),
        __('One passport-size photo with a white background (35 mm × 45 mm).'),
        __('Offer letter from your university or college.'),
        __('Health Declaration Form (from EMGS).'),
        __('Academic certificates and transcripts for your previous studies.'),
        __('English language certificate (for example IELTS, TOEFL iBT, PTE Academic or MUET), uploaded with your eVAL application.'),
        __('Personal bond, if your institution asks for one.'),
    ];
    $passSteps = [
        [__('Apply online'), __('Create an account and apply for the Student Pass on the EMGS website, after you accept your offer.')],
        [__('Upload documents'), __('Upload all the required documents.')],
        [__('eVAL issued'), __('The Immigration Department of Malaysia issues your electronic Visa Approval Letter (eVAL).')],
        [__('Single Entry Visa'), __('Apply for the Single Entry Visa. Bangladeshi nationals can apply for an e-Visa.')],
        [__('Arrival card'), __('Apply for the Malaysia Digital Arrival Card (MDAC) within 3 days before you arrive.')],
        [__('Travel'), __('Travel to Malaysia — only after you have received your eVAL.')],
        [__('Medical screening'), __('Post-arrival medical screening at an EMGS panel clinic within 7 working days of arrival.')],
        [__('Student Pass endorsement'), __('Malaysian Immigration endorses your Student Pass. Your institution assists you.')],
    ];
    $keyFacts = [
        [__('Who needs it'), __('A Student Pass is needed for programmes longer than 3 months.'), 'steps'],
        [__('eVAL validity'), __('The eVAL is valid for 6 months from the date it is issued.'), 'faq'],
        [__('Processing time'), __('EMGS\'s client charter is 14 working days for a complete application.'), 'faq'],
        [__('Insurance'), __('Insurance coverage is compulsory for all international students throughout their studies.'), 'faq'],
    ];
    $tabs = ['courses' => __('Courses'), 'requirements' => __('Requirements'), 'documents' => __('Documents'), 'student-pass' => __('Student Pass'), 'living-cost' => __('Living cost'), 'apply' => __('Apply')];
@endphp

@section('content')
<main class="v2-sg">
    <section class="v2-sg-hero" aria-labelledby="my-title">
        <div class="v2-shell v2-sg-hero-grid">
            <div>
                <nav class="v2-crumbs v2-crumbs-light" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('Study in Malaysia') }}</span></nav>
                <h1 id="my-title">{{ __('Study in Malaysia from Bangladesh') }}</h1>
                <p>{{ __('Diplomas, foundation, bachelor\'s and master\'s programmes at Malaysian universities and colleges. We prepare your EMGS Student Pass application with you, from offer letter to arrival.') }}</p>
                <ul class="v2-chips">
                    @if($intakes)<li>{{ __('Intakes: :intakes', ['intakes' => $intakes]) }}</li>@endif
                    <li>{{ __('Student Pass applied online through EMGS') }}</li>
                </ul>
                <div class="v2-actions"><a class="v2-btn v2-btn-red" href="#apply">{{ __('Book a free consultation') }}</a></div>
            </div>
            <img class="v2-sg-photo" src="{{ asset('images/malaysia/kuala-lumpur-skyline.webp') }}" alt="{{ __('Kuala Lumpur skyline, Malaysia') }}" width="1200" height="800" loading="eager" decoding="async">
        </div>
    </section>

    <nav class="v2-tabs" aria-label="{{ __('On this page') }}">
        <div class="v2-shell">@foreach($tabs as $anchor => $label)<a href="#{{ $anchor }}">{{ $label }}</a>@endforeach</div>
    </nav>

    <section class="v2-section" id="courses" aria-labelledby="courses-title">
        <div class="v2-shell">
            <h2 id="courses-title" class="v2-h2">{{ __('Find a course') }}</h2>
            @if($courses->isNotEmpty())
                <div class="v2-filter" role="group" aria-label="{{ __('Filter by level') }}" data-course-filter>
                    <button type="button" aria-pressed="true" data-level="">{{ __('All') }}</button>
                    @foreach($courseLevels as $level)<button type="button" aria-pressed="false" data-level="{{ $level }}">{{ __($levels[$level] ?? ucfirst($level)) }}</button>@endforeach
                </div>
                <div class="v2-courses">
                    @foreach($courses as $course)
                        <article class="v2-course" data-course-level="{{ $course->study_level }}">
                            <div class="v2-course-top">
                                @if($course->study_level)<span class="v2-pill v2-pill-level">{{ __($levels[$course->study_level] ?? $course->study_level) }}</span>@endif
                                @if($course->intakes->first())<small>{{ __('Next intake: :month', ['month' => $course->intakes->first()->starts_on->translatedFormat('M Y')]) }}</small>@endif
                            </div>
                            <h3>{{ $course->name }}</h3>
                            <p>{{ $course->institution?->name }}@if($course->duration_months) · {{ trans_choice(':count month|:count months', $course->duration_months, ['count' => $course->duration_months]) }}@endif</p>
                            <div class="v2-course-foot"><strong>{{ $course->tuition_note ?: __('Fees on request') }}</strong><a class="v2-link" href="#apply">{{ __('Ask about this') }} →</a></div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="v2-card v2-empty">
                    <p>{{ __('We match you with courses at Malaysian universities and colleges based on your results and budget. Tell us what you want to study and your consultant sends a shortlist.') }}</p>
                    <a class="v2-btn v2-btn-red" href="#apply">{{ __('Get my course shortlist') }}</a>
                </div>
            @endif
        </div>
    </section>

    <section class="v2-section v2-section-white" aria-label="{{ __('Requirements and documents') }}">
        <div class="v2-shell v2-split v2-split-top">
            <div id="requirements">
                <h2 class="v2-h2">{{ __('Entry requirements') }}</h2>
                <dl class="v2-reqs">
                    @foreach($keyFacts as [$label, $text, $source])<div><dt>{{ $label }}</dt><dd>{{ $text }} <a class="v2-source" href="{{ $sources[$source]['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Source: EMGS') }}</a></dd></div>@endforeach
                    <div><dt>{{ __('Academic') }}</dt><dd>{{ __('Each university or college sets its own academic entry requirements. Your consultant checks them for your chosen course.') }}</dd></div>
                </dl>
            </div>
            <div id="documents" class="v2-checklist">
                <h2 class="v2-h2">{{ __('Document checklist') }}</h2>
                <ul>@foreach($documents as $document)<li><label><input type="checkbox"> {{ $document }}</label></li>@endforeach</ul>
                <p class="v2-small">{{ __('Based on the EMGS guidelines. Your institution may ask for more.') }} <a class="v2-source" href="{{ $sources['guidelines']['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Source: EMGS') }}</a></p>
                <button type="button" class="v2-btn v2-btn-outline" data-print-checklist>{{ __('Print or save as PDF') }}</button>
            </div>
        </div>
    </section>

    <section class="v2-section" id="student-pass" aria-labelledby="pass-title">
        <div class="v2-shell">
            <h2 id="pass-title" class="v2-h2">{{ __('Student Pass (EMGS) – what happens') }}</h2>
            <ol class="v2-pass v2-pass-8">
                @foreach($passSteps as [$title, $text])<li><span>{{ __('Step :number', ['number' => $loop->iteration]) }}</span><strong>{{ $title }}</strong><p>{{ $text }}</p></li>@endforeach
            </ol>
            <p class="v2-notice">{{ __('Student Pass approval is decided by the Immigration Department of Malaysia through EMGS, and admission by each institution. If the medical screening is not passed, the Student Pass is not endorsed (an appeal can be made within 3 days of the result). We prepare a complete application with you, but we cannot guarantee approval.') }}</p>
            <p class="v2-small">{{ __('EMGS sets the Student Pass fees. Check the current amounts on the official calculator:') }} <a class="v2-link" href="{{ $sources['fees']['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('EMGS fee calculator') }} →</a></p>
            @if($guides->isNotEmpty())
                <div class="v2-guides">
                    <h3>{{ __('Step-by-step guides') }}</h3>
                    <ul>@foreach($guides as $guide)<li><a href="{{ $guide->publicUrl() }}">{{ $guide->localized('title') }} →</a></li>@endforeach</ul>
                </div>
            @endif
            <details class="v2-sources">
                <summary>{{ __('Official sources for this page') }}</summary>
                <ul>@foreach($sources as $source)<li><a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $source['label'] }}</a></li>@endforeach</ul>
            </details>
        </div>
    </section>

    <section class="v2-section v2-section-navy" aria-label="{{ __('Living cost and application') }}">
        <div class="v2-shell v2-split v2-split-top">
            <div id="living-cost">
                <h2 class="v2-h2">{{ __('Monthly living cost (estimate)') }}</h2>
                @if($living !== [])
                    <table class="v2-living">
                        <caption class="sg-visually-hidden">{{ __('Monthly living cost (estimate)') }}</caption>
                        <tbody>@foreach($living as $row)<tr><th scope="row">{{ $row['label'] }}</th><td>{{ $row['value'] }}</td></tr>@endforeach</tbody>
                    </table>
                    <p class="v2-small v2-small-light">{{ __('Estimates only. Costs depend on your area, housing and lifestyle.') }}</p>
                @else
                    <p class="v2-lead-light">{{ __('Living costs depend on your city, housing and lifestyle. Your consultant gives you a written monthly estimate in MYR with your course shortlist.') }}</p>
                @endif
            </div>
            <x-site.consultation-form destination="MY" source="malaysia" anchor="apply" :title="__('Apply for a free consultation')" :qualification="true" />
        </div>
    </section>
</main>
@endsection
