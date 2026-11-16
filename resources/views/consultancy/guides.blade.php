@extends('layouts.site')

@section('title', __('Singapore Study Guides'))
@section('meta_description', __('Step-by-step Singapore study guides for Bangladeshi students: admission, Student\'s Pass, courses, costs and living, checked against official sources.'))
@section('body_class', 'public-page-body egho-page-body')
@if(request()->hasAny(['q', 'topic']))
@section('robots', 'noindex,follow')
@endif

@push('head')
    @if($results->isNotEmpty())
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Singapore Study Guides',
        'itemListElement' => $results->values()->map(fn ($item, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'url' => $item['entry']->publicUrl(), 'name' => $item['entry']->title])->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')
@php($img = static fn (string $id): string => 'https://images.unsplash.com/photo-'.$id.'?auto=format&fit=crop&w=300&q=80')
@php($guideImages = ['1496939376851-89342e90adcd', '1508964942454-1a56651d54ac', '1525625293386-3f8f99389edd', '1541339907198-e08756dedf3f', '1565967511849-76a60a516170'])
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('SINGAPORE STUDENT CONSULTANCY') }}</span>
                <h1>{{ __('Singapore Study Guides') }}</h1>
                <p>{{ __('Step-by-step guides on admission, the Student\'s Pass, courses, costs and living in Singapore, checked against official sources such as ICA and MOE.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route('consultancy.singapore') }}">{{ __('Study in Singapore') }}</a> › <span aria-current="page">{{ __('Singapore Guides') }}</span></nav>

        @if($total > 0)
        <form method="GET" action="{{ route('consultancy.guides.index') }}" class="sg-card sg-filter" role="search">
            <label class="egho-field sg-filter-grow"><span>{{ __('Search guides') }}</span><input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="{{ __('e.g. Student\'s Pass, tuition grant, polytechnic') }}"></label>
            <label class="egho-field"><span>{{ __('Topic') }}</span><select name="topic"><option value="">{{ __('All topics') }}</option>@foreach($topics as $topicOption)<option value="{{ $topicOption['key'] }}" @selected($filters['topic'] === $topicOption['key'])>{{ $topicOption['label'] }}</option>@endforeach</select></label>
            <button type="submit" class="sg-btn sg-btn-red">{{ __('Search') }}</button>
            @if($filters['q'] !== '' || $filters['topic'] !== '')<a class="sg-btn sg-btn-outline" href="{{ route('consultancy.guides.index') }}">{{ __('Clear') }}</a>@endif
        </form>

        <div class="sg-chips" aria-label="{{ __('Topics') }}">
            <a href="{{ route('consultancy.guides.index') }}" @class(['sg-chip', 'is-active' => $filters['topic'] === ''])>{{ __('All topics') }}</a>
            @foreach($topics as $topicOption)
                <a href="{{ route('consultancy.guides.index', ['topic' => $topicOption['key']]) }}" @class(['sg-chip', 'is-active' => $filters['topic'] === $topicOption['key']])>{{ $topicOption['label'] }}</a>
            @endforeach
        </div>
        @endif

        <section class="sg-section-tight" aria-live="polite">
            @if($total === 0)
                <div class="sg-card sg-empty">
                    <h2>{{ __('Guides are on the way') }}</h2>
                    <p>{{ __('Guides are being reviewed against official sources and will be published soon. Ask our consultants in the meantime.') }}</p>
                    <a class="sg-btn sg-btn-red" href="{{ route('consultancy.singapore') }}#consultation">{{ __('Request a free consultation') }}</a>
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
            <div class="sg-cta-actions"><a class="sg-btn sg-btn-red" href="{{ route('consultancy.singapore') }}#consultation">{{ __('Free Consultation') }}</a><a class="sg-btn sg-btn-outline" href="{{ route('consultancy.institutions.index') }}">{{ __('Institutions') }}</a></div>
        </section>
    </div>
</main>
@endsection
