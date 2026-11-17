@extends('layouts.site')

@php
    $isMalaysia = ($study['code'] ?? 'SG') === 'MY';
    $text = $isMalaysia ? [
        'title' => __('Malaysia Study Guides'),
        'description' => __('Step-by-step Malaysia study guides for Bangladeshi students: the EMGS Student Pass, eVAL, documents, arrival and medical screening, based on official EMGS information.'),
        'eyebrow' => __('MALAYSIA STUDENT CONSULTANCY'),
        'lead' => __('Step-by-step guides on the EMGS Student Pass, the eVAL, documents, arrival and living in Malaysia, based on official information from EMGS.'),
        'study' => __('Study in Malaysia'), 'short' => __('Malaysia Guides'),
    ] : [
        'title' => __('Singapore Study Guides'),
        'description' => __('Step-by-step Singapore study guides for Bangladeshi students: admission, Student\'s Pass, courses, costs and living, checked against official sources.'),
        'eyebrow' => __('SINGAPORE STUDENT CONSULTANCY'),
        'lead' => __('Step-by-step guides on admission, the Student\'s Pass, courses, costs and living in Singapore, checked against official sources such as ICA and MOE.'),
        'study' => __('Study in Singapore'), 'short' => __('Singapore Guides'),
    ];
@endphp
@section('title', $text['title'])
@section('meta_description', $text['description'])
@section('body_class', 'public-page-body egho-page-body')
@if(request()->hasAny(['q', 'topic']))
@section('robots', 'noindex,follow')
@endif

@push('head')
    @if($results->isNotEmpty())
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => $text['title'],
        'itemListElement' => $results->values()->map(fn ($item, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'url' => $item['entry']->publicUrl(), 'name' => $item['entry']->title])->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')
@php($img = static fn (string $file): string => asset('images/'.$file))
@php($guideImages = $isMalaysia ? ['malaysia/kuala-lumpur-skyline.webp', 'malaysia/kuala-lumpur-day.webp', 'malaysia/petronas-towers-night.webp'] : ['singapore/marina-bay.webp'])
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ $text['eyebrow'] }}</span>
                <h1>{{ $text['title'] }}</h1>
                <p>{{ $text['lead'] }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route($study['page']) }}">{{ $text['study'] }}</a> › <span aria-current="page">{{ $text['short'] }}</span></nav>

        @if($total > 0)
        <form method="GET" action="{{ route($study['guides']) }}" class="sg-card sg-filter" role="search">
            <label class="egho-field sg-filter-grow"><span>{{ __('Search guides') }}</span><input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="{{ __('e.g. Student\'s Pass, tuition grant, polytechnic') }}"></label>
            <label class="egho-field"><span>{{ __('Topic') }}</span><select name="topic"><option value="">{{ __('All topics') }}</option>@foreach($topics as $topicOption)<option value="{{ $topicOption['key'] }}" @selected($filters['topic'] === $topicOption['key'])>{{ $topicOption['label'] }}</option>@endforeach</select></label>
            <button type="submit" class="sg-btn sg-btn-red">{{ __('Search') }}</button>
            @if($filters['q'] !== '' || $filters['topic'] !== '')<a class="sg-btn sg-btn-outline" href="{{ route($study['guides']) }}">{{ __('Clear') }}</a>@endif
        </form>

        <div class="sg-chips" aria-label="{{ __('Topics') }}">
            <a href="{{ route($study['guides']) }}" @class(['sg-chip', 'is-active' => $filters['topic'] === ''])>{{ __('All topics') }}</a>
            @foreach($topics as $topicOption)
                <a href="{{ route($study['guides'], ['topic' => $topicOption['key']]) }}" @class(['sg-chip', 'is-active' => $filters['topic'] === $topicOption['key']])>{{ $topicOption['label'] }}</a>
            @endforeach
        </div>
        @endif

        <section class="sg-section-tight" aria-live="polite">
            @if($total === 0)
                <div class="sg-card sg-empty">
                    <h2>{{ __('Guides are on the way') }}</h2>
                    <p>{{ __('Guides are being reviewed against official sources and will be published soon. Ask our consultants in the meantime.') }}</p>
                    <a class="sg-btn sg-btn-red" href="{{ route($study['page']) }}#apply">{{ __('Request a free consultation') }}</a>
                </div>
            @elseif($results->isEmpty())
                <div class="sg-card sg-empty"><h2>{{ __('No guides match your search') }}</h2><p>{{ __('Try another word or topic.') }}</p></div>
            @else
                <p class="sg-note">{{ trans_choice(':count guide|:count guides', $results->count(), ['count' => $results->count()]) }}</p>
                <div class="sg-guide-list">
                    @foreach($results as $item)
                        <a class="sg-card sg-guide" href="{{ $item['entry']->publicUrl() }}">
                            <img src="{{ $img($guideImages[$loop->index % count($guideImages)]) }}" alt="" width="96" height="68" loading="lazy" decoding="async">
                            <span><small class="sg-guide-topic">{{ $item['topic']['label'] }}</small><strong>{{ $item['entry']->localized('title') }}</strong><span>{{ \Illuminate\Support\Str::limit((string) $item['entry']->localized('summary'), 140) }}</span></span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="sg-card sg-cta-row">
            <div><h2>{{ __('Need help with your application?') }}</h2><p>{{ __('Our consultants explain the steps and help you prepare. Admission and Student\'s Pass decisions are made by the institution and ICA.') }}</p></div>
            <div class="sg-cta-actions"><a class="sg-btn sg-btn-red" href="{{ route($study['page']) }}#apply">{{ __('Free Consultation') }}</a><a class="sg-btn sg-btn-outline" href="{{ route($study['institutions']) }}">{{ __('Institutions') }}</a></div>
        </section>
    </div>
</main>
@endsection
