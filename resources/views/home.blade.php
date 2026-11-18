@extends('layouts.site')

@section('title', data_get($homeContent, 'seo.title', 'Study in Malaysia from Bangladesh | Eagle Global Hub'))
@section('meta_description', data_get($homeContent, 'seo.description'))
@section('canonical', data_get($homeContent, 'seo.canonical') ?: url()->current())
@section('robots', data_get($homeContent, 'seo.robots', 'index,follow'))
@section('og_title', data_get($homeContent, 'seo.og_title'))
@section('og_description', data_get($homeContent, 'seo.og_description'))
@section('og_image', data_get($homeContent, 'seo.og_image'))
@section('twitter_card', data_get($homeContent, 'seo.twitter_card', 'summary_large_image'))
@section('body_class', 'sg-home-body sg-body-v2')

@php
    $settings = data_get($homeContent, 'settings', []);
    $facts = app(\App\Services\SiteFacts::class);
    $pageContent = app(\App\Services\Content\PageContent::class);
    $siteContact = $siteContact ?? [];
    $studyUrl = route('consultancy.malaysia');
    $whatsappUrl = $facts->whatsappUrl(__('Hello Eagle Global Hub, I would like a free consultation.'));

    // News ticker: only real news written by an admin, never the default welcome line.
    $newsEnabledValue = data_get($settings, 'news_enabled', true);
    $newsEnabled = is_bool($newsEnabledValue) ? $newsEnabledValue : ! in_array(strtolower(trim((string) $newsEnabledValue)), ['', '0', 'false', 'off', 'no'], true);
    $newsText = trim((string) data_get($settings, 'news_text', ''));
    $showNews = $newsEnabled && $newsText !== '' && ! str_starts_with($newsText, 'Welcome to Eagle Global Hub');
    $newsLabel = trim((string) data_get($settings, 'news_label', '')) ?: __('Latest News');
    $newsUrl = trim((string) data_get($settings, 'news_url', ''));
    $newsLinkLabel = trim((string) data_get($settings, 'news_link_label', '')) ?: __('Learn More');
    $newsTickerDuration = max(8, min(120, (int) data_get($settings, 'news_ticker_duration', 20)));
    $pageAssetVersion = static fn (string $file): string => ($modifiedAt = @filemtime(public_path($file))) ? '?v='.$modifiedAt : '';

    // Hero numbers: shown only when the owner has entered them.
    $stats = array_values(array_filter([
        $facts->get('students_guided') ? ['value' => $facts->get('students_guided').'+', 'label' => __('students guided')] : null,
        $facts->get('partner_institutions') ? ['value' => $facts->get('partner_institutions'), 'label' => __('partner institutions')] : null,
        $facts->get('founded_year') ? ['value' => $facts->get('founded_year'), 'label' => __('serving since')] : null,
    ]));

    $trust = array_values(array_filter([
        $facts->get('trade_licence_no') ? ['icon' => 'shield', 'text' => __('Registered company · Trade licence :number', ['number' => $facts->get('trade_licence_no')])] : null,
        filled(data_get($siteContact, 'address')) ? ['icon' => 'pin', 'text' => __('Walk-in office in Gulshan, Dhaka')] : null,
        ['icon' => 'doc', 'text' => __('Written fee quote before you pay')],
        ['icon' => 'chat', 'text' => __('Bangla & English support')],
    ]));

    // Service cards from Admin › Menus (homepage tiles); repeated links or titles are shown once.
    $services = app(\App\Services\Content\HomepageServiceCards::class)->cards();

    // How it works: editable as the page section "how-it-works" ("- Title: text | time" per line).
    // Default timings are our own process or EMGS's published figures; admission time is set by each institution.
    $steps = collect($pageContent->points('how-it-works'))->map(function (array $step): array {
        [$text, $time] = array_pad(array_map('trim', explode('|', $step['text'], 2)), 2, '');

        return ['title' => $step['title'], 'text' => $text, 'time' => $time];
    })->all() ?: [
        ['title' => __('Free consultation'), 'text' => __('Goals, budget and eligibility check.'), 'time' => __('Day 1')],
        ['title' => __('Course shortlist'), 'text' => __('Malaysian universities, intakes and written cost estimate.'), 'time' => __('Week 1')],
        ['title' => __('Offer letter'), 'text' => __('Admission application and documents to your institution.'), 'time' => ''],
        ['title' => __('Student Pass & eVAL'), 'text' => __('EMGS application prepared and tracked.'), 'time' => __('14 working days (EMGS charter)')],
        ['title' => __('Fly & settle'), 'text' => __('Ticket, housing and the medical screening after arrival.'), 'time' => __('Screening within 7 working days')],
    ];

    // Yearly costs in Malaysia (MYR), entered in Admin › Business facts.
    $costs = $facts->rows([
        'my_cost_tuition' => __('Tuition (diploma, private)'),
        'my_cost_housing' => __('Housing (shared room)'),
        'my_cost_food' => __('Food & transport'),
        'my_cost_visa' => __('Student Pass, insurance & medical'),
    ], 'MYR ');
    if ($facts->get('our_fee')) {
        $costs[] = ['label' => __('Our service fee'), 'value' => (preg_match('/^[\d.,\s–-]+$/u', $facts->get('our_fee')) === 1 ? 'BDT ' : '').$facts->get('our_fee')];
    }
    $costTotal = $facts->get('my_cost_total');

    $stories = $pageContent->published(\App\Enums\ContentType::SuccessStory)->take(3);
    $googleRating = $facts->googleRating();
    // Only real team photos are shown; without any, the team section is hidden.
    $team = rescue(fn () => \App\Models\TeamMember::query()->published()->whereNotNull('photo_path')->where('photo_path', '!=', '')->limit(4)->get(), collect(), false);
    $faqs = $pageContent->published(\App\Enums\ContentType::Faq)->take(5);
    $address = data_get($siteContact, 'address');
    $directionsUrl = filled($address) ? 'https://www.google.com/maps/search/?api=1&query='.urlencode((string) $address) : null;
    $organizationSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => ['TravelAgency', 'EducationalOrganization'],
        'name' => $siteName ?? 'Eagle Global Hub LTD',
        'url' => route('home'),
        'logo' => $siteLogo ?? null,
        'email' => data_get($siteContact, 'email'),
        'telephone' => data_get($siteContact, 'phone'),
        'address' => $address ? ['@type' => 'PostalAddress', 'streetAddress' => $address] : null,
    ]);
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/malaysia/petronas-towers-night.webp') }}" type="image/webp">
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@if($showNews)
@push('head')<link rel="stylesheet" href="{{ asset('css/egh-news-ticker.css').$pageAssetVersion('css/egh-news-ticker.css') }}">@endpush
<section class="egho-news-ticker" style="--egho-news-duration: {{ $newsTickerDuration }}s" aria-label="{{ $newsLabel }}">
    <div class="egho-shell egho-news-ticker-inner">
        <strong class="egho-news-ticker-label">{{ $newsLabel }}</strong>
        <div class="egho-news-ticker-viewport">
            <div class="egho-news-ticker-track">
                @foreach ([false, true] as $duplicateGroup)
                    <div class="egho-news-ticker-group" @if($duplicateGroup) aria-hidden="true" @endif>
                        @for ($tickerRepeat = 0; $tickerRepeat < 4; $tickerRepeat++)
                            <span @if($duplicateGroup || $tickerRepeat > 0) aria-hidden="true" @endif>{{ $newsText }}</span>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>
        @if($newsUrl !== '')<a class="egho-news-ticker-link" href="{{ $newsUrl }}">{{ $newsLinkLabel }}</a>@endif
    </div>
</section>
@endif

<main class="v2-home">
    <section class="v2-hero v2-hero-photo" style="--v2-hero-image: url('{{ asset('images/malaysia/petronas-towers-night.webp') }}')" aria-labelledby="hero-title">
        <div class="v2-shell v2-hero-grid">
            <div class="v2-hero-copy">
                <span class="v2-pill v2-pill-light">{{ __('Malaysia student consultancy from Bangladesh') }}</span>
                <h1 id="hero-title">{{ __('Study in Malaysia, with a team that walks every step with you.') }}</h1>
                <p>{{ __('University and college choice, admission documents, your EMGS Student Pass, air ticket and housing — one office in Gulshan, one consultant from start to arrival. Singapore too.') }}</p>
                <div class="v2-actions">
                    <a class="v2-btn v2-btn-red" href="#consultation-form">{{ __('Book a free consultation') }}</a>
                    @if($whatsappUrl)<a class="v2-btn v2-btn-ghost-light" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" d="M4 20l1.3-3.8A8 8 0 1 1 8 19.2z"/></svg>{{ __('Chat on WhatsApp') }}</a>@endif
                </div>
                @if($stats !== [])
                <dl class="v2-stats">
                    @foreach($stats as $stat)<div><dt>{{ $stat['label'] }}</dt><dd>{{ $stat['value'] }}</dd></div>@endforeach
                </dl>
                @endif
            </div>
            <x-site.consultation-form source="home" anchor="consultation-form" />
        </div>
    </section>

    <section class="v2-trust" aria-label="{{ __('Why you can trust us') }}">
        <ul class="v2-shell">
            @foreach($trust as $item)<li><x-site.art :name="$item['icon']" />{{ $item['text'] }}</li>@endforeach
        </ul>
    </section>

    @if(! in_array(strtolower(trim((string) data_get($settings, 'show_services', '1'))), ['0', 'false', 'off', 'no'], true))
    <section class="v2-section" aria-labelledby="services-title">
        <div class="v2-shell">
            <div class="v2-head">
                <div><span class="v2-eyebrow">{{ __('What we do') }}</span><h2 id="services-title" class="v2-h2">{{ __('Everything you need to study abroad') }}</h2></div>
                <a class="v2-link" href="{{ route('travel-services.index') }}">{{ __('All services') }} →</a>
            </div>
            <div class="v2-services">
                @foreach($services as $service)
                    @php([$serviceBody, $serviceCta] = [$service['body'], $service['cta']])
                    <a class="v2-service-card" href="{{ $service['url'] }}" @if($service['new_tab']) target="_blank" rel="noopener noreferrer" @endif>
                        <span class="v2-service-top"><x-site.art :name="$service['icon']" /><span class="v2-service-no" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></span>
                        <h3>{{ $service['title'] }}</h3>
                        @if($serviceBody !== '')<p>{{ $serviceBody }}</p>@endif
                        <span class="v2-service-cta">{{ $serviceCta }} →</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="v2-section v2-section-navy" id="how-it-works" aria-labelledby="how-title">
        <div class="v2-shell">
            <div class="v2-head-center"><span class="v2-eyebrow">{{ __('How it works') }}</span><h2 id="how-title" class="v2-h2">{{ __('From first call to landing in Malaysia') }}</h2></div>
            <ol class="v2-timeline">
                @foreach($steps as $step)
                    <li><span class="v2-step-no" aria-hidden="true">{{ $loop->iteration }}</span><strong>{{ $step['title'] }}</strong>@if($step['text'] !== '')<p>{{ $step['text'] }}</p>@endif @if($step['time'] !== '')<small>{{ $step['time'] }}</small>@endif</li>
                @endforeach
            </ol>
            <p class="v2-disclaimer">{{ __('Admission is decided by each institution. The Student Pass is approved by Malaysia\'s Immigration Department through EMGS. We prepare and guide — we never promise approval.') }}</p>
        </div>
    </section>

    <section class="v2-section v2-section-white" aria-labelledby="costs-title">
        <div class="v2-shell v2-split">
            <div>
                <span class="v2-eyebrow">{{ __('Clear costs') }}</span>
                <h2 id="costs-title" class="v2-h2">{{ __('Know the budget before you start') }}</h2>
                <p class="v2-lead">{{ $costs !== [] ? __('A rough yearly estimate in Malaysian ringgit for a diploma student. Your consultant gives a written quote for your exact course.') : __('Costs depend on the course and institution. Your consultant gives you a written estimate for tuition, living costs and our fee before you pay anything.') }}</p>
                <a class="v2-link" href="#consultation-form">{{ __('Get my exact estimate') }} →</a>
            </div>
            @if($costs !== [])
            <table class="v2-costs">
                <caption class="sg-visually-hidden">{{ __('Estimated yearly costs') }}</caption>
                <tbody>
                    @foreach($costs as $row)<tr><th scope="row">{{ $row['label'] }}</th><td>{{ $row['value'] }}</td></tr>@endforeach
                </tbody>
                @if($costTotal)<tfoot><tr><th scope="row">{{ __('Estimated total / year') }}</th><td>{{ preg_match('/^[\d.,\s–-]+$/u', $costTotal) === 1 ? 'MYR ' : '' }}{{ $costTotal }}</td></tr></tfoot>@endif
            </table>
            @else
            <div class="v2-estimate">
                <strong>{{ __('Tuition · Housing · Living costs · Student Pass · Our fee') }}</strong>
                <p>{{ __('All five in one written estimate for your course, free and before you decide.') }}</p>
                <a class="v2-btn v2-btn-red" href="#consultation-form">{{ __('Ask for my estimate') }}</a>
            </div>
            @endif
        </div>
    </section>

    @if($stories->isNotEmpty() || $googleRating)
    <section class="v2-section" aria-labelledby="stories-title">
        <div class="v2-shell">
            <span class="v2-eyebrow">{{ __('Success stories') }}</span>
            <h2 id="stories-title" class="v2-h2">{{ __('Students who made it') }}</h2>
            @if($stories->isNotEmpty())
            <div class="v2-stories">
                @foreach($stories as $story)
                    <figure class="v2-story">
                        <blockquote>“{{ \Illuminate\Support\Str::limit((string) ($story->localized('summary') ?: strip_tags((string) $story->localized('body'))), 220) }}”</blockquote>
                        <figcaption><strong>{{ $story->localized('title') }}</strong>@if($story->category)<span>{{ $story->category }}</span>@endif</figcaption>
                    </figure>
                @endforeach
            </div>
            @endif
            @if($googleRating)
                <p class="v2-rating"><strong>{{ __('Google rating :rating ★', ['rating' => $googleRating['rating']]) }}</strong> · {{ __('from :count reviews', ['count' => $googleRating['count']]) }} · <a href="{{ $googleRating['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Read on Google') }}</a></p>
            @endif
        </div>
    </section>
    @endif

    @if($team->isNotEmpty() || $faqs->isNotEmpty())
    <section class="v2-section v2-section-white" aria-label="{{ __('Team and questions') }}">
        <div class="v2-shell {{ $team->isNotEmpty() && $faqs->isNotEmpty() ? 'v2-split v2-split-top' : '' }}">
            @if($team->isNotEmpty())
            <div>
                <span class="v2-eyebrow">{{ __('Meet your consultants') }}</span>
                <h2 class="v2-h2">{{ __('Real people, real office') }}</h2>
                <ul class="v2-team">
                    @foreach($team as $member)
                        <li>
                            @if($member->photo_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}" alt="{{ $member->name }}" loading="lazy" decoding="async">@else<span class="v2-team-initial" aria-hidden="true">{{ mb_substr($member->name, 0, 1) }}</span>@endif
                            <strong>{{ $member->name }}</strong><span>{{ $member->role_title }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if($faqs->isNotEmpty())
            <div>
                <span class="v2-eyebrow">{{ __('FAQ') }}</span>
                <h2 class="v2-h2">{{ __('Common questions') }}</h2>
                <div class="sg-faq">
                    @foreach($faqs as $faq)
                        <details @if($loop->first) open @endif><summary>{{ $faq->localized('title') }}</summary><div class="sg-faq-answer"><x-content-body :text="$faq->localized('body')" /></div></details>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>
    @endif

    <section class="v2-section" aria-labelledby="visit-title">
        <div class="v2-shell">
            <div class="v2-contact-band">
                <div>
                    <h2 id="visit-title">{{ __('Visit us or call today') }}</h2>
                    @if(filled($address))<p>{{ $address }}</p>@endif
                    @if($facts->get('office_hours'))<p>{{ $facts->get('office_hours') }}</p>@endif
                    <div class="v2-actions">
                        @if($facts->phone())<a class="v2-btn v2-btn-white" href="{{ $facts->telUrl() }}">{{ __('Call :phone', ['phone' => $facts->phone()]) }}</a>@endif
                        @if($whatsappUrl)<a class="v2-btn v2-btn-navy" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('WhatsApp us') }}</a>@endif
                        @if(! $facts->mapEmbedUrl() && $directionsUrl)<a class="v2-btn v2-btn-ghost-light" href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Get directions') }}</a>@endif
                    </div>
                </div>
                @if($facts->mapEmbedUrl())
                    <iframe class="v2-map" src="{{ $facts->mapEmbedUrl() }}" title="{{ __('Map of our office') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection
