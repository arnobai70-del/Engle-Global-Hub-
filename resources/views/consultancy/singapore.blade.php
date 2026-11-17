@extends('layouts.site')

@section('title', __('Study in Singapore from Bangladesh'))
@section('meta_description', __('Diplomas, bachelor\'s and master\'s programmes in Singapore for Bangladeshi students: course finder, entry requirements, document checklist, Student\'s Pass steps, living costs and a free consultation.'))
@section('body_class', 'sg-body-v2')

@push('head')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Service', 'name' => 'Study in Singapore consultancy', 'serviceType' => 'Student consultancy', 'areaServed' => 'Bangladesh', 'url' => route('consultancy.singapore'), 'provider' => ['@type' => 'EducationalOrganization', 'name' => $siteName ?? 'Eagle Global Hub LTD', 'url' => route('home')]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@php
    $levels = \App\Models\StudentEnquiry::STUDY_LEVELS;
    $courseLevels = collect(['diploma', 'bachelor', 'master'])->merge($courses->pluck('study_level')->filter())->unique()->values();
    $requirements = [
        [__('Diploma'), __('SSC / HSC or O/A-Level pass. English proof as set by the institution.')],
        [__('Bachelor\'s'), __('HSC or A-Level, or a relevant diploma.')],
        [__('Master\'s'), __('Recognised bachelor\'s degree; work experience for some MBAs.')],
        [__('English'), __('IELTS / PTE, or the institution\'s own test where accepted.')],
    ];
    $documents = [__('Passport (valid 6+ months)'), __('SSC & HSC certificates and transcripts'), __('Degree certificate (if any)'), __('English test result'), __('Bank statement / sponsor letter'), __('Birth certificate'), __('Passport-size photos')];
    $passSteps = [
        [__('Offer letter'), __('The institution issues your offer and registers you with ICA.')],
        [__('eForm 16'), __('We help you complete the online application.')],
        [__('In-principle approval'), __('ICA reviews it; you pay the issuance fee.')],
        [__('Arrival'), __('Medical check and Student\'s Pass card in Singapore.')],
    ];
    $tabs = ['courses' => __('Courses'), 'requirements' => __('Requirements'), 'documents' => __('Documents'), 'students-pass' => __('Student\'s Pass'), 'living-cost' => __('Living cost'), 'apply' => __('Apply')];
@endphp

@section('content')
<main class="v2-sg">
    <section class="v2-sg-hero" aria-labelledby="sg-title">
        <div class="v2-shell v2-sg-hero-grid">
            <div>
                <nav class="v2-crumbs v2-crumbs-light" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('Study in Singapore') }}</span></nav>
                <h1 id="sg-title">{{ __('Study in Singapore from Bangladesh') }}</h1>
                <p>{{ __('Diplomas, bachelor\'s and master\'s programmes at polytechnics, private institutions and universities. We handle the paperwork with you, from shortlist to Student\'s Pass.') }}</p>
                <ul class="v2-chips">
                    @if($intakes)<li>{{ __('Intakes: :intakes', ['intakes' => $intakes]) }}</li>@endif
                    <li>{{ __('IELTS alternatives accepted at some institutions') }}</li>
                </ul>
                <div class="v2-actions"><a class="v2-btn v2-btn-red" href="#apply">{{ __('Book a free consultation') }}</a></div>
            </div>
            <img class="v2-sg-photo" src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1100&q=80" alt="{{ __('Marina Bay, Singapore') }}" width="1100" height="700" loading="eager" decoding="async">
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
                            <div class="v2-course-foot">
                                <strong>{{ $course->tuition_note ?: __('Fees on request') }}</strong>
                                <a class="v2-link" href="#apply">{{ __('Ask about this') }} →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="v2-card v2-empty">
                    <p>{{ __('We match you with courses at polytechnics, private institutions and universities based on your results and budget. Tell us what you want to study and your consultant sends a shortlist.') }}</p>
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
                    @foreach($requirements as [$level, $text])<div><dt>{{ $level }}</dt><dd>{{ $text }}</dd></div>@endforeach
                </dl>
                <p class="v2-small">{{ __('Each institution sets its own requirements. Your consultant checks them for your chosen course.') }}</p>
            </div>
            <div id="documents" class="v2-checklist">
                <h2 class="v2-h2">{{ __('Document checklist') }}</h2>
                <ul>
                    @foreach($documents as $document)<li><label><input type="checkbox"> {{ $document }}</label></li>@endforeach
                </ul>
                <button type="button" class="v2-btn v2-btn-outline" data-print-checklist>{{ __('Print or save as PDF') }}</button>
            </div>
        </div>
    </section>

    <section class="v2-section" id="students-pass" aria-labelledby="pass-title">
        <div class="v2-shell">
            <h2 id="pass-title" class="v2-h2">{{ __('Student\'s Pass (ICA) – what happens') }}</h2>
            <ol class="v2-pass">
                @foreach($passSteps as [$title, $text])<li><span>{{ __('Step :number', ['number' => $loop->iteration]) }}</span><strong>{{ $title }}</strong><p>{{ $text }}</p></li>@endforeach
            </ol>
            <p class="v2-notice">{{ __('Singapore\'s Immigration & Checkpoints Authority (ICA) decides every Student\'s Pass, and each institution decides admission. We prepare a complete application with you, but we cannot guarantee admission or a Student\'s Pass.') }}</p>
            @if($destinations->count() > 1)
                <div class="v2-guides">
                    <h3>{{ __('Other study destinations') }}</h3>
                    <ul class="v2-country-list">@foreach($destinations as $country)@if($country->iso2 !== 'SG')<li>{{ __($country->name) }}</li>@endif @endforeach</ul>
                    <p class="v2-small"><a class="v2-link" href="{{ route('services.show', 'student-visa') }}">{{ __('Student visa help for these countries') }} →</a></p>
                </div>
            @endif
            @if($guides->isNotEmpty())
                <div class="v2-guides">
                    <h3>{{ __('Step-by-step guides') }}</h3>
                    <ul>@foreach($guides as $guide)<li><a href="{{ $guide->publicUrl() }}">{{ $guide->localized('title') }} →</a></li>@endforeach</ul>
                </div>
            @endif
        </div>
    </section>

    <section class="v2-section v2-section-navy" aria-label="{{ __('Living cost and application') }}">
        <span id="consultation" class="v2-anchor" aria-hidden="true"></span>
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
                    <p class="v2-lead-light">{{ __('Living costs depend on your area, housing and lifestyle. Your consultant gives you a written monthly estimate with your course shortlist.') }}</p>
                @endif
            </div>
            <x-site.consultation-form source="singapore" anchor="apply" :title="__('Apply for a free consultation')" :qualification="true" />
        </div>
    </section>
</main>
@endsection
