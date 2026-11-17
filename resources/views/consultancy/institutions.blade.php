@extends('layouts.site')

@php
    $isMalaysia = ($study['code'] ?? 'SG') === 'MY';
    $text = $isMalaysia ? [
        'title' => __('Institutions in Malaysia'),
        'description' => __('Malaysian universities and colleges verified by Eagle Global Hub LTD for students from Bangladesh, with active courses, open intakes and admission guidance.'),
        'eyebrow' => __('MALAYSIA STUDENT CONSULTANCY'),
        'notice' => __('Listing an institution here does not mean a partnership or an agency agreement. Admission decisions are made by each institution, and Student Pass approval by Malaysia\'s Immigration Department through EMGS.'),
        'study' => __('Study in Malaysia'), 'guides' => __('Malaysia Guides'),
    ] : [
        'title' => __('Institutions in Singapore'),
        'description' => __('Singapore institutions verified by Eagle Global Hub LTD for students from Bangladesh, with active courses, open intakes and admission guidance.'),
        'eyebrow' => __('SINGAPORE STUDENT CONSULTANCY'),
        'notice' => __('Listing an institution here does not mean a partnership or an agency agreement. Admission decisions are made by each institution, and Student\'s Pass decisions by Singapore\'s Immigration & Checkpoints Authority (ICA).'),
        'study' => __('Study in Singapore'), 'guides' => __('Singapore Guides'),
    ];
@endphp
@section('title', $text['title'])
@section('meta_description', $text['description'])
@section('body_class', 'public-page-body egho-page-body')

@section('content')
@php($levels = \App\Models\StudentEnquiry::STUDY_LEVELS)
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ $text['eyebrow'] }}</span>
                <h1>{{ __('Institutions') }}</h1>
                <p>{{ __('Polytechnics, universities and colleges whose details our team has checked against the institution\'s official website, with their active courses and open intakes.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route($study['page']) }}">{{ $text['study'] }}</a> › <span aria-current="page">{{ __('Institutions') }}</span></nav>

        <p class="sg-card sg-notice" role="note">{{ $text['notice'] }}</p>

        <section class="sg-section-tight" aria-labelledby="institutions-title">
            <h2 id="institutions-title" class="sg-visually-hidden">{{ __('Institutions') }}</h2>
            @if($institutions->isEmpty())
                <div class="sg-card sg-empty">
                    <h2>{{ __('Institution details are being verified') }}</h2>
                    <p>{{ __('We publish an institution only after checking its details against its official website. Ask our consultants about polytechnics, universities and private institutions in the meantime.') }}</p>
                    <a class="sg-btn sg-btn-red" href="{{ route($study['page']) }}#apply">{{ __('Request a free consultation') }}</a>
                </div>
            @else
                <div class="sg-grid sg-grid-2">
                    @foreach($institutions as $institution)
                        <article class="sg-card sg-institution">
                            <header>
                                <span class="sg-icon sg-icon-navy"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M4 10h16M12 3 3 8h18zM6 10v8M10 10v8M14 10v8M18 10v8"/></svg></span>
                                <div><h3>{{ $institution->name }}</h3>@if($institution->country)<p>{{ __($institution->country->name) }}</p>@endif</div>
                            </header>
                            @if($institution->website_url)<a class="sg-link-arrow" href="{{ $institution->website_url }}" target="_blank" rel="noopener noreferrer">{{ __('Official website') }}</a>@endif
                            @if($institution->courses->isNotEmpty())
                                <h4>{{ __('Courses & Intakes') }}</h4>
                                <ul class="sg-course-list">
                                    @foreach($institution->courses as $course)
                                        <li>
                                            <strong>{{ $course->name }}</strong>
                                            <span>{{ __($levels[$course->study_level] ?? ucfirst((string) $course->study_level)) }}@if($course->duration_months) · {{ trans_choice(':count month|:count months', $course->duration_months, ['count' => $course->duration_months]) }}@endif</span>
                                            @if($course->intakes->isNotEmpty())<span>{{ __('Open intakes') }}: {{ $course->intakes->map(fn ($intake) => $intake->starts_on->translatedFormat('M Y'))->implode(', ') }}</span>@else<span>{{ __('No open intake listed') }}</span>@endif
                                            @if($course->source_url)<a href="{{ $course->source_url }}" target="_blank" rel="noopener noreferrer">{{ __('Course page') }}</a>@endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="sg-note">{{ __('Courses for this institution have not been added yet.') }}</p>
                            @endif
                            <a class="sg-btn sg-btn-red sg-btn-sm" href="{{ route($study['page']) }}#apply">{{ __('Ask about this institution') }}</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="sg-card sg-cta-row" aria-labelledby="admission-guidance">
            <div>
                <h2 id="admission-guidance">{{ __('Admission guidance') }}</h2>
                @if($admissionGuides->isNotEmpty())
                    <ul class="sg-inline-links">@foreach($admissionGuides as $guide)<li><a href="{{ $guide->publicUrl() }}">{{ $guide->localized('title') }}</a></li>@endforeach</ul>
                @else
                    <p>{{ __('Our consultants explain entry requirements, documents and timelines for each institution.') }}</p>
                @endif
            </div>
            <div class="sg-cta-actions"><a class="sg-btn sg-btn-red" href="{{ route($study['page']) }}#apply">{{ __('Free Consultation') }}</a><a class="sg-btn sg-btn-outline" href="{{ route($study['guides']) }}">{{ $text['guides'] }}</a></div>
        </section>
    </div>
</main>
@endsection
