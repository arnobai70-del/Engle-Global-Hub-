@extends('layouts.site')

@section('title', $page['title'])
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($page['intro']), 155))
@section('body_class', 'sg-body-v2')
{{-- Travel, visa and business pages get a matching footer band; study pages keep "Ready to study in Malaysia?". --}}
@if(($page['group'] ?? '') !== 'study')
@section('footer_cta')
    <div class="sg-footer-cta">
        <div><h2>{{ __('Need help with :service?', ['service' => $page['title']]) }}</h2><p>{{ __('Tell us what you need. Our team replies with options and a written quote.') }}</p></div>
        <div class="sg-footer-cta-actions"><a href="#quote-form" class="sg-btn sg-btn-red">{{ __('Get a quote') }}</a>@if($wa = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello, I would like a quote for :service.', ['service' => $page['title']])))<a href="{{ $wa }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
    </div>
@endsection
@endif

@php
    $whatsappUrl = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello, I would like help with :service.', ['service' => $page['title']]));
    $parent = in_array($page['group'], ['study'], true)
        ? ['label' => __('Study in Malaysia'), 'url' => route('consultancy.malaysia')]
        : ['label' => __('Visa & Travel'), 'url' => route('travel-services.index')];
@endphp

@section('content')
<main class="v2-service">
    <div class="v2-shell">
        <nav class="v2-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <a href="{{ $parent['url'] }}">{{ $parent['label'] }}</a> / <span aria-current="page">{{ $page['title'] }}</span></nav>

        <section class="v2-service-hero" aria-labelledby="service-title">
            <div>
                <span class="v2-pill v2-pill-light">{{ ($liveSearchUrl ?? null) ? __('Book online or ask us') : __('Enquiry service') }}</span>
                <h1 id="service-title">{{ $page['headline'] }}</h1>
                @if($page['intro'] !== '')<p>{{ $page['intro'] }}</p>@endif
                <div class="v2-actions">
                    <a class="v2-btn v2-btn-red" href="#quote-form">{{ __('Send an enquiry') }}</a>
                    @if($whatsappUrl)<a class="v2-btn v2-btn-wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('WhatsApp us') }}</a>@endif
                    @if($liveSearchUrl ?? null)<a class="v2-btn v2-btn-ghost-light" href="{{ $liveSearchUrl }}">{{ __('Search online') }}</a>@endif
                </div>
            </div>
            <dl class="v2-fact-tiles">
                @foreach($page['facts'] as $fact)
                    <div><dt>{{ $fact['label'] }}</dt><dd>{{ $fact['value'] }}</dd></div>
                @endforeach
            </dl>
        </section>

        <div class="v2-service-grid">
            <div class="v2-service-main">
                @if($page['included'] !== [])
                <section class="v2-card" aria-labelledby="included-title">
                    <h2 id="included-title" class="v2-card-h2">{{ __('What\'s included') }}</h2>
                    <ul class="v2-ticks">@foreach($page['included'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                </section>
                @endif

                @if(($cmsEntry ?? null) !== null)
                <section class="v2-card" aria-labelledby="details-title">
                    <h2 id="details-title" class="v2-card-h2">{{ $cmsEntry->localized('title') }}</h2>
                    <x-content-body :text="$cmsEntry->localized('body')" />
                </section>
                @endif

                <section class="v2-card" aria-labelledby="how-title">
                    <h2 id="how-title" class="v2-card-h2">{{ __('How it works') }}</h2>
                    <ol class="v2-steps-list">
                        @foreach($page['steps'] as [$stepTitle, $stepText])
                            <li><span class="v2-step-no" aria-hidden="true">{{ $loop->iteration }}</span><div><strong>{{ $stepTitle }}</strong><p>{{ $stepText }}</p></div></li>
                        @endforeach
                    </ol>
                </section>

                <section class="v2-card" aria-labelledby="documents-title">
                    <h2 id="documents-title" class="v2-card-h2">{{ __('Documents you\'ll usually need') }}</h2>
                    <ul class="v2-doc-list">@foreach($page['documents'] as $document)<li>{{ $document }}</li>@endforeach</ul>
                    <p class="v2-small">{{ __('The exact list depends on your case. Your consultant sends the final checklist.') }}</p>
                </section>

                @if($page['topics'] !== [] && in_array($page['group'], ['visa', 'study'], true))
                <section class="v2-card" aria-labelledby="countries-title">
                    <h2 id="countries-title" class="v2-card-h2">{{ __('Countries we help with') }}</h2>
                    <ul class="v2-country-list">@foreach($page['topics'] as $country)<li>{{ $country }}</li>@endforeach</ul>
                </section>
                @endif

                <p class="v2-notice">{{ $page['disclaimer'] }}</p>

                @if($page['related'] !== [])
                <section aria-labelledby="related-title">
                    <h2 id="related-title" class="v2-related-title">{{ __('Related services') }}</h2>
                    <div class="v2-related">
                        @foreach($page['related'] as $related)<a href="{{ $related['url'] }}">{{ $related['title'] }} <span aria-hidden="true">→</span></a>@endforeach
                    </div>
                </section>
                @endif
            </div>

            <aside class="v2-service-aside">
                <x-site.quote-form :service="$page['quote_service']" :service-title="$page['title']" :topic-label="$page['topic_label']" :topics="$page['topics']" />
            </aside>
        </div>
    </div>
</main>
@endsection
