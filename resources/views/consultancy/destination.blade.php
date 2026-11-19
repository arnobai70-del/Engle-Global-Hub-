@extends('layouts.site')

{{--
    /study-in-{slug}: the generic study destination page. Same layout and classes as Study in Malaysia;
    every section comes from Admin › Study destinations and is left out while it has no content.
--}}
@php
    // In sentences: "the United Kingdom" (Admin › Study destinations › Name in sentences), otherwise the plain name.
    $country = $destination->sentenceName();
    // :country (sentence name) and :in_country (Bengali place form, or "name:" when not set) for every country sentence.
    $names = $destination->sentenceReplacements();
    $title = $destination->localized('hero_title') ?? __('Study in :country from Bangladesh', $names);
    $intro = (string) $destination->localized('intro');
    $section = fn (string $key) => $destination->section($key)->filter(fn ($item) => filled($item->title))->values();
    $why = $section('why');
    $subjects = $section('subject');
    $requirements = $section('requirement');
    $documents = $section('document');
    $steps = $section('visa_step');
    $workRules = $section('work_rule');
    $faqs = $section('faq')->filter(fn ($item) => filled($item->body))->values();
    $intakes = $destination->localized('intakes');
    $visaName = $destination->localized('visa_name');
    $authority = $destination->localized('visa_authority');
    $processing = $destination->localized('processing_time');
    $hero = $destination->heroImage;
    // Official sources: the checked source rows, then every source a fact on this page links to.
    $sources = $destination->section('source')->toBase()->filter(fn ($item) => $item->isVerifiedSource())
        ->map(fn ($item) => ['url' => $item->source_url, 'label' => $item->sourceLabel()])
        ->merge($destination->items->toBase()->filter(fn ($item) => $item->section !== 'source' && filled($item->source_url) && str_starts_with((string) $item->source_url, 'https://'))->map(fn ($item) => ['url' => $item->source_url, 'label' => $item->sourceLabel()]))
        ->when($destination->processing_source_url, fn ($list) => $list->push(['url' => $destination->processing_source_url, 'label' => $destination->processing_source_label ?: parse_url($destination->processing_source_url, PHP_URL_HOST)]))
        ->unique('url')->values();
    $tabs = array_filter([
        'why' => $why->isNotEmpty() || $subjects->isNotEmpty() ? __('Why :country', $names) : null,
        'requirements' => $requirements->isNotEmpty() ? __('Requirements') : null,
        'documents' => $documents->isNotEmpty() ? __('Documents') : null,
        'visa' => $steps->isNotEmpty() ? __('Student visa') : null,
        'costs' => $costs->isNotEmpty() ? __('Costs') : null,
        'work' => $workRules->isNotEmpty() ? __('Work rights') : null,
        'faq' => $faqs->isNotEmpty() ? __('FAQ') : null,
        'apply' => __('Apply'),
    ]);
    $pageUrl = route('study-destinations.show', $destination->slug);
    $schema = app(\App\Services\SiteSchema::class);
@endphp

@section('title', $title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($intro), 170, '…'))
@section('body_class', 'sg-body-v2')
@section('study_country', $country)
@section('study_in_country', $names['in_country'])
@if($hero)
@section('og_image', $hero->url())
@endif

@push('head')
{!! $schema->script([
    $schema->breadcrumbList([[__('Home'), route('home')], [__('Study in :country', $names), $pageUrl]]),
    $schema->faqPage($faqs),
]) !!}
@endpush

@section('content')
<main class="v2-sg" data-study-destination="{{ $destination->slug }}">
    <section class="v2-sg-hero" aria-labelledby="destination-title">
        <div class="v2-shell v2-sg-hero-grid">
            <div>
                <nav class="v2-crumbs v2-crumbs-light" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> / <span aria-current="page">{{ __('Study in :country', $names) }}</span></nav>
                <h1 id="destination-title">{{ $title }}</h1>
                <x-content-body :text="$intro" />
                <ul class="v2-chips">
                    @if($intakes)<li>{{ __('Intakes: :intakes', ['intakes' => $intakes]) }}</li>@endif
                    @if($visaName)<li>{{ $visaName }}@if($authority) · {{ $authority }}@endif</li>@endif
                </ul>
                <div class="v2-actions"><a class="v2-btn v2-btn-red" href="#apply">{{ __('Book a free consultation') }}</a></div>
            </div>
            @if($hero)
                <img class="v2-sg-photo" src="{{ $hero->url() }}" alt="{{ $destination->localized('hero_alt') }}" @if($hero->width && $hero->height) width="{{ $hero->width }}" height="{{ $hero->height }}" @endif loading="eager" decoding="async">
            @endif
        </div>
    </section>

    <nav class="v2-tabs" aria-label="{{ __('On this page') }}">
        <div class="v2-shell">@foreach($tabs as $anchor => $label)<a href="#{{ $anchor }}">{{ $label }}</a>@endforeach</div>
    </nav>

    @if($why->isNotEmpty() || $subjects->isNotEmpty() || $institutionCount > 0)
    <section class="v2-section" id="why" aria-label="{{ __('Why :country', $names) }}">
        <div class="v2-shell v2-split v2-split-top">
            @if($why->isNotEmpty())
            <div data-section="why">
                <h2 class="v2-h2">{{ __('Why study in :country', $names) }}</h2>
                <ul class="v2-ticks">@foreach($why as $item)<li><strong>{{ $item->localized('title') }}</strong>@if($item->localized('body')) {{ $item->localized('body') }}@endif</li>@endforeach</ul>
            </div>
            @endif
            <div>
                @if($subjects->isNotEmpty())
                <div data-section="subjects">
                    <h2 class="v2-h2">{{ __('Popular subjects and levels') }}</h2>
                    <ul class="v2-ticks">@foreach($subjects as $item)<li><strong>{{ $item->localized('title') }}</strong>@if($item->localized('body')) {{ $item->localized('body') }}@endif</li>@endforeach</ul>
                </div>
                @endif
                @if($institutionCount > 0)
                <div class="v2-card" data-section="institutions">
                    <h3>{{ __('Universities and colleges') }}</h3>
                    <p>{{ trans_choice(':count institution listed|:count institutions listed', $institutionCount, ['count' => $institutionCount]) }}</p>
                    <a class="v2-link" href="{{ route('study-destinations.institutions.index', $destination->slug) }}">{{ __('See institutions') }} →</a>
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if($requirements->isNotEmpty() || $documents->isNotEmpty())
    <section class="v2-section v2-section-white" aria-label="{{ __('Requirements and documents') }}">
        <div class="v2-shell v2-split v2-split-top">
            @if($requirements->isNotEmpty())
            <div id="requirements" data-section="requirements">
                <h2 class="v2-h2">{{ __('Entry requirements') }}</h2>
                <dl class="v2-reqs">
                    @foreach($requirements as $item)<div><dt>{{ $item->localized('title') }}</dt><dd>{{ $item->localized('body') }} @include('consultancy.partials.source', ['item' => $item])</dd></div>@endforeach
                </dl>
            </div>
            @endif
            @if($documents->isNotEmpty())
            <div id="documents" class="v2-checklist" data-section="documents">
                <h2 class="v2-h2">{{ __('Document checklist') }}</h2>
                <ul>@foreach($documents as $item)<li><label><input type="checkbox"> {{ $item->localized('title') }}</label></li>@endforeach</ul>
                <button type="button" class="v2-btn v2-btn-outline" data-print-checklist>{{ __('Print or save as PDF') }}</button>
            </div>
            @endif
        </div>
    </section>
    @endif

    @if($steps->isNotEmpty())
    <section class="v2-section" id="visa" aria-labelledby="visa-title" data-section="visa">
        <div class="v2-shell">
            {{-- A fixed, short heading; the admin's visa name (which can be long) goes underneath. --}}
            <h2 id="visa-title" class="v2-h2">{{ __('Student visa: step by step') }}</h2>
            @if($visaName)<p class="v2-pill v2-pill-enquiry v2-visa-name" data-visa-name>{{ $visaName }}</p>@endif
            @if($authority || $processing)
                <dl class="v2-reqs">
                    @if($authority)<div><dt>{{ __('Decided by') }}</dt><dd>{{ $authority }}</dd></div>@endif
                    @if($processing)<div><dt>{{ __('Processing time') }}</dt><dd>{{ $processing }} @if($destination->processing_source_url)<a class="v2-source" href="{{ $destination->processing_source_url }}" target="_blank" rel="noopener noreferrer">{{ __('Source: :source', ['source' => $destination->processing_source_label ?: parse_url($destination->processing_source_url, PHP_URL_HOST)]) }}</a>@endif</dd></div>@endif
                </dl>
            @endif
            <ol class="v2-pass">
                @foreach($steps as $item)<li><span>{{ __('Step :number', ['number' => $loop->iteration]) }}</span><strong>{{ $item->localized('title') }}</strong>@if($item->localized('body'))<p>{{ $item->localized('body') }}</p>@endif @include('consultancy.partials.source', ['item' => $item])</li>@endforeach
            </ol>
            <p class="v2-notice">{{ __('Visa decisions are made by the authorities of :country, and admission by each institution. We help you prepare a complete application; we cannot guarantee approval.', $names) }}</p>
        </div>
    </section>
    @endif

    @if($costs->isNotEmpty() || $workRules->isNotEmpty())
    <section class="v2-section v2-section-white" aria-label="{{ __('Costs and work rights') }}">
        <div class="v2-shell v2-split v2-split-top">
            @if($costs->isNotEmpty())
            <div id="costs" data-section="costs">
                <h2 class="v2-h2">{{ __('Costs (estimate)') }}</h2>
                <table class="v2-living v2-living-light">
                    <caption class="sg-visually-hidden">{{ __('Costs (estimate)') }}</caption>
                    <tbody>@foreach($costs as $cost)<tr><th scope="row">{{ $cost['item']->localized('title') }}@if($cost['item']->localized('body'))<br><small>{{ $cost['item']->localized('body') }}</small>@endif</th><td>{{ $cost['amount'] }}@if($cost['bdt'])<br><small>≈ {{ $cost['bdt'] }}</small>@endif @include('consultancy.partials.source', ['item' => $cost['item']])</td></tr>@endforeach</tbody>
                </table>
                <p class="v2-small">{{ __('Estimates only. Costs depend on your institution, city, housing and lifestyle.') }}@if($rate && $destination->currency_code) {{ __('Taka at 1 :currency = BDT :rate', ['currency' => $destination->currency_code, 'rate' => rtrim(rtrim(number_format((float) $rate, 4), '0'), '.')]) }}@if($destination->bdt_rate_checked_on) ({{ __('checked on :date', ['date' => $destination->bdt_rate_checked_on->translatedFormat('j M Y')]) }})@endif.@endif</p>
            </div>
            @endif
            @if($workRules->isNotEmpty())
            <div id="work" data-section="work">
                <h2 class="v2-h2">{{ __('Work while studying') }}</h2>
                <dl class="v2-reqs">
                    @foreach($workRules as $item)<div><dt>{{ $item->localized('title') }}</dt><dd>{{ $item->localized('body') }} @include('consultancy.partials.source', ['item' => $item])</dd></div>@endforeach
                </dl>
            </div>
            @endif
        </div>
    </section>
    @endif

    @if($faqs->isNotEmpty() || $guides->isNotEmpty() || $sources->isNotEmpty())
    <section class="v2-section" id="faq" aria-label="{{ __('Questions, guides and sources') }}">
        <div class="v2-shell">
            @if($faqs->isNotEmpty())
            <div data-section="faq">
                <h2 class="v2-h2">{{ __('Common questions') }}</h2>
                <div class="sg-faq">
                    @foreach($faqs as $item)<details @if($loop->first) open @endif><summary>{{ $item->localized('title') }}</summary><div class="sg-faq-answer"><x-content-body :text="$item->localized('body')" /></div></details>@endforeach
                </div>
            </div>
            @endif
            @if($guides->isNotEmpty())
                <div class="v2-guides" data-section="guides">
                    <h3>{{ __('Step-by-step guides') }}</h3>
                    <ul>@foreach($guides as $guide)<li><a href="{{ $guide->publicUrl() }}">{{ $guide->localized('title') }} →</a></li>@endforeach</ul>
                    <a class="v2-link" href="{{ route('study-destinations.guides.index', $destination->slug) }}">{{ __('All guides') }} →</a>
                </div>
            @endif
            @if($sources->isNotEmpty())
            <details class="v2-sources" data-section="sources">
                <summary>{{ __('Official sources for this page') }}</summary>
                <ul>@foreach($sources as $source)<li><a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">{{ $source['label'] }}</a></li>@endforeach</ul>
            </details>
            @endif
        </div>
    </section>
    @endif

    <section class="v2-section v2-section-navy" aria-label="{{ __('Apply') }}">
        <div class="v2-shell v2-split v2-split-top">
            <div>
                <h2 class="v2-h2">{{ __('Plan your studies in :country', $names) }}</h2>
                <p class="v2-lead-light">{{ __('Tell us your results and budget. A consultant checks the entry requirements for your course and explains the visa steps and costs in writing.') }}</p>
            </div>
            <x-site.consultation-form :destination="$study['code']" source="study-destination" anchor="apply" :title="__('Apply for a free consultation')" :qualification="true" />
        </div>
    </section>
</main>
@endsection
