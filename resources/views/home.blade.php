@extends('layouts.site')

@section('title', data_get($homeContent, 'seo.title', 'Flights & Travel'))
@section('meta_description', data_get($homeContent, 'seo.description'))
@section('canonical', data_get($homeContent, 'seo.canonical') ?: url()->current())
@section('robots', data_get($homeContent, 'seo.robots', 'index,follow'))
@section('og_title', data_get($homeContent, 'seo.og_title'))
@section('og_description', data_get($homeContent, 'seo.og_description'))
@section('og_image', data_get($homeContent, 'seo.og_image'))
@section('twitter_card', data_get($homeContent, 'seo.twitter_card', 'summary_large_image'))
@section('body_class', 'sg-home-body')

@php
    $settings = data_get($homeContent, 'settings', []);
    $newsEnabledValue = data_get($settings, 'news_enabled', true);
    $newsEnabled = is_bool($newsEnabledValue)
        ? $newsEnabledValue
        : ! in_array(strtolower(trim((string) $newsEnabledValue)), ['0', 'false', 'off', 'no'], true);
    $newsLabel = trim((string) data_get($settings, 'news_label', 'Latest News'));
    $newsText = trim((string) data_get($settings, 'news_text', 'Welcome to Eagle Global Hub LTD — Travel & Visa Services.'));
    $newsUrl = trim((string) data_get($settings, 'news_url', ''));
    $newsLinkLabel = trim((string) data_get($settings, 'news_link_label', 'Learn More'));
    $newsTickerDuration = max(8, min(120, (int) data_get($settings, 'news_ticker_duration', 20)));
    $showSection = static fn (string $section): bool => ! in_array(strtolower(trim((string) data_get($settings, 'show_'.$section, '1'))), ['0', 'false', 'off', 'no'], true);

    $travelServices = $travelServices ?? [];
    $serviceLink = static function (string $key) use ($travelServices): ?string {
        $routeName = $travelServices[$key]['page_route_name'] ?? $travelServices[$key]['route_name'] ?? null;

        return is_string($routeName) && \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : null;
    };
    $flightsVisible = app(\App\Services\Feature\FeatureManager::class)->isVisibleTo('flights', auth()->user());
    $flightsUrl = auth()->check() ? (auth()->user()->can('flights.search') ? route('flights.index') : route('dashboard')) : route('login');
    $providerStatus = app(\App\Services\Integrations\ProviderStatus::class);
    $serviceStatus = static fn (string $key): string => $providerStatus->of($key);
    $studyUrl = route('consultancy.singapore');
    $consultationUrl = $studyUrl.'#consultation';

    $pageContent = app(\App\Services\Content\PageContent::class);
    $guides = $pageContent->published(\App\Enums\ContentType::Guide)->take(4);
    $studentsPassGuide = $guides->firstWhere('slug', 'students-pass-guidance');
    $destinations = rescue(fn () => \App\Models\Destination::query()->published()->limit(3)->get(), collect(), false);
    $img = static fn (string $id, int $width = 1200): string => 'https://images.unsplash.com/photo-'.$id.'?auto=format&fit=crop&w='.$width.'&q=80';
    $heroImage = $img('1565967511849-76a60a516170', 1920);
    $guideImages = ['1496939376851-89342e90adcd', '1508964942454-1a56651d54ac', '1525625293386-3f8f99389edd', '1541339907198-e08756dedf3f'];

    $pageAssetVersion = static function (string $file): string {
        $modifiedAt = @filemtime(public_path($file));

        return $modifiedAt ? '?v='.$modifiedAt : '';
    };
    $organizationSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        'name' => $siteName ?? 'Eagle Global Hub LTD',
        'url' => route('home'),
        'logo' => $siteLogo ?? null,
        'email' => data_get($siteContact ?? [], 'email'),
        'telephone' => data_get($siteContact ?? [], 'phone'),
        'address' => data_get($siteContact ?? [], 'address') ? ['@type' => 'PostalAddress', 'streetAddress' => data_get($siteContact, 'address')] : null,
    ]);
    $icons = [
        'cap' => '<path d="M2 9.5 12 5l10 4.5-10 4.5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9.5V15"/>',
        'id' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 16c.6-1.6 1.7-2.4 3-2.4s2.4.8 3 2.4M14 10h4M14 13.5h4"/>',
        'passport' => '<rect x="5" y="3" width="14" height="18" rx="2"/><circle cx="12" cy="10" r="3.2"/><path d="M8.8 10h6.4M12 6.8c1 .9 1.4 2 1.4 3.2s-.4 2.3-1.4 3.2c-1-.9-1.4-2-1.4-3.2s.4-2.3 1.4-3.2M9 17h6"/>',
        'plane' => '<path d="M10.5 13.5 3 11l1.5-1.5 7.5.8 4.5-4.8a2 2 0 0 1 2.8 2.8L14.5 12.8l.8 7.5L13.8 22l-2.5-7.5"/>',
        'bed' => '<path d="M3 18V7M3 14h18v4M21 14v-2.5A2.5 2.5 0 0 0 18.5 9H11v5"/><circle cx="7" cy="11" r="2"/>',
        'mountain' => '<path d="m3 19 6-9 4 5 2.5-3L21 19z"/><circle cx="16.5" cy="6.5" r="1.8"/>',
        'leaf' => '<path d="M12 21V11"/><path d="M12 11c-4 0-7-2.5-7-7 4.5 0 7 3 7 7zM12 14c3.5 0 6-2 6-6-4 0-6 2.5-6 6z"/><path d="M5 21h14"/>',
        'doc' => '<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'support' => '<path d="M4 13a8 8 0 0 1 16 0"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M19 19c0 1.5-2 2.5-5 2.5"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'visa' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14.5 3v4.5H19"/><path d="M9 13h6M9 16.5h4"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5h6v2M3 12h18"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
    $svg = static fn (string $name): string => '<svg viewBox="0 0 24 24" aria-hidden="true">'.$icons[$name].'</svg>';
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ $heroImage }}">
    <link rel="stylesheet" href="{{ asset('css/egh-news-ticker.css').$pageAssetVersion('css/egh-news-ticker.css') }}">
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@if($newsEnabled && $newsText !== '')
<section class="egho-news-ticker" style="--egho-news-duration: {{ $newsTickerDuration }}s" aria-label="{{ $newsLabel !== '' ? $newsLabel : 'Latest News' }}">
    <div class="egho-shell egho-news-ticker-inner">
        <strong class="egho-news-ticker-label">{{ $newsLabel !== '' ? $newsLabel : 'Latest News' }}</strong>
        <div class="egho-news-ticker-viewport">
            <div class="egho-news-ticker-track">
                @foreach ([false, true] as $duplicateGroup)
                    <div class="egho-news-ticker-group" @if($duplicateGroup) aria-hidden="true" @endif>
                        @for ($tickerRepeat = 0; $tickerRepeat < 6; $tickerRepeat++)
                            <span @if($duplicateGroup || $tickerRepeat > 0) aria-hidden="true" @endif>{{ $newsText }}</span>
                        @endfor
                    </div>
                @endforeach
            </div>
        </div>
        @if($newsUrl !== '')
            <a class="egho-news-ticker-link" href="{{ $newsUrl }}">{{ $newsLinkLabel !== '' ? $newsLinkLabel : 'Learn More' }}</a>
        @endif
    </div>
</section>
@endif

<main class="sg-home">
    <section class="sg-hero" style="--sg-home-hero: url('{{ $heroImage }}')" aria-labelledby="sg-hero-title">
        <div class="sg-shell">
            <div class="sg-hero-copy">
                <span class="sg-badge">{{ __('Singapore Student Consultancy') }}</span>
                <h1 id="sg-hero-title">{{ __('Your Gateway to Study in Singapore') }}</h1>
                <p class="sg-hero-lead">{{ __('From admission to arrival — we support your entire study journey to Singapore.') }}</p>
                <ul class="sg-checklist sg-checklist-light">
                    <li>{{ __('Admission & Institution Support') }}</li>
                    <li>{{ __('Student\'s Pass (ICA) Guidance') }}</li>
                    <li>{{ __('Documents & Application Support') }}</li>
                    <li>{{ __('Air Ticket & Accommodation') }}</li>
                    <li>{{ __('Arrival & Post-Arrival Support') }}</li>
                </ul>
                <div class="sg-hero-actions">
                    <a href="{{ $consultationUrl }}" class="sg-btn sg-btn-red">{{ __('Apply Now') }} {!! $svg('arrow') !!}</a>
                    <a href="{{ $consultationUrl }}" class="sg-btn sg-btn-white">{!! $svg('support') !!} {{ __('Free Consultation') }}</a>
                </div>
            </div>
        </div>
        <div class="sg-hero-dots" aria-hidden="true"><span></span><span></span><span></span></div>
    </section>

    <div class="sg-shell sg-tiles-wrap">
        <nav class="sg-tiles" aria-label="{{ __('Our Services') }}">
            @php($tileMenu = app(\App\Services\Navigation\MenuService::class)->tree('home_tiles'))
            @if($tileMenu !== null)
                @foreach($tileMenu as $tile)<a class="sg-tile{{ $loop->first ? ' is-featured' : '' }}" href="{{ $tile['url'] }}" @if($tile['new_tab']) target="_blank" rel="noopener noreferrer" @endif><x-site.icon :name="$tile['icon'] ?? 'globe'" /><span>{{ $tile['label'] }}</span></a>@endforeach
            @else
            <a class="sg-tile is-featured" href="{{ $studyUrl }}">{!! $svg('cap') !!}<span>{{ __('Study in Singapore') }}</span></a>
            <a class="sg-tile" href="{{ $studentsPassGuide?->publicUrl() ?? $studyUrl.'#guides' }}">{!! $svg('id') !!}<span>{{ __('Student\'s Pass (ICA)') }}</span></a>
            <a class="sg-tile" href="{{ route('visa.index') }}">{!! $svg('passport') !!}<span>{{ __('Visa Services') }}</span></a>
            @if($flightsVisible)<a class="sg-tile" href="{{ $flightsUrl }}">{!! $svg('plane') !!}<span>{{ __('Flight Services') }}</span></a>@endif
            <a class="sg-tile" href="{{ $serviceLink('hotels') ?? route('service-enquiries.create', 'hotel-booking') }}">{!! $svg('bed') !!}<span>{{ __('Hotels & Homes') }}</span></a>
            <a class="sg-tile" href="{{ $serviceLink('tours') ?? route('service-enquiries.create', 'tour-package') }}">{!! $svg('mountain') !!}<span>{{ __('Tour Packages') }}</span></a>
            <a class="sg-tile" href="{{ route('explore-bangladesh.index') }}">{!! $svg('leaf') !!}<span>{{ __('Explore Bangladesh') }}</span></a>
            @endif
        </nav>
    </div>

    <section class="sg-section sg-section-white" aria-labelledby="sg-consultancy-title">
        <div class="sg-shell sg-split">
            <a class="sg-media-card" href="{{ $studyUrl }}" aria-label="{{ __('Study in Singapore') }}">
                <img src="{{ $img('1525625293386-3f8f99389edd', 1200) }}" alt="{{ __('Marina Bay, Singapore') }}" width="1200" height="750" loading="lazy" decoding="async">
                <span class="sg-play"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15l12-7.5z"/></svg></span>
            </a>
            <div>
                <h2 id="sg-consultancy-title">{{ __('Complete Student Consultancy for') }} <span class="sg-accent">{{ __('Singapore') }}</span></h2>
                <p>{{ __('A complete, reliable and transparent support system for Bangladeshi students who want to study in Singapore.') }}</p>
                <ul class="sg-checklist">
                    <li>{{ __('Choose the right course and institution') }}</li>
                    <li>{{ __('Application and admission support') }}</li>
                    <li>{{ __('Student\'s Pass (ICA) guidance') }}</li>
                    <li>{{ __('Travel, accommodation and arrival support') }}</li>
                </ul>
                <a href="{{ $studyUrl }}" class="sg-btn sg-btn-ghost-red">{{ __('Learn More') }} {!! $svg('arrow') !!}</a>
            </div>
        </div>
    </section>

    @if($showSection('services'))
    <section class="sg-section" aria-labelledby="sg-services-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <h2 id="sg-services-title">{{ __('Our Complete Services') }}</h2>
                <p>{{ __('End-to-end solutions for your study, visa and travel needs.') }}</p>
            </div>
            <div class="sg-grid sg-grid-6">
                <a class="sg-card sg-feature" href="{{ $studyUrl }}"><span class="sg-icon">{!! $svg('cap') !!}</span><h3>{{ __('Admission Support') }}</h3><p>{{ __('Course and institution selection, application guidance.') }}</p></a>
                <a class="sg-card sg-feature" href="{{ $studentsPassGuide?->publicUrl() ?? $studyUrl.'#guides' }}"><span class="sg-icon sg-icon-navy">{!! $svg('id') !!}</span><h3>{{ __('Student\'s Pass (ICA) Support') }}</h3><p>{{ __('Application preparation and formalities support.') }}</p></a>
                <a class="sg-card sg-feature" href="{{ $consultationUrl }}"><span class="sg-icon sg-icon-navy">{!! $svg('doc') !!}</span><h3>{{ __('Documents Support') }}</h3><p>{{ __('Document checklist, preparation and verification.') }}</p></a>
                <a class="sg-card sg-feature" href="{{ $flightsVisible ? $flightsUrl : $consultationUrl }}"><span class="sg-icon">{!! $svg('plane') !!}</span><h3>{{ __('Air Ticket Booking') }}</h3><p>{{ __('Flight search, booking and itinerary support.') }}</p></a>
                <a class="sg-card sg-feature" href="{{ route('service-enquiries.create', 'hotel-booking') }}"><span class="sg-icon">{!! $svg('bed') !!}</span><h3>{{ __('Accommodation') }}</h3><p>{{ __('Safe and student-friendly housing options.') }}</p></a>
                <a class="sg-card sg-feature" href="{{ $consultationUrl }}"><span class="sg-icon sg-icon-green">{!! $svg('pin') !!}</span><h3>{{ __('Arrival Support') }}</h3><p>{{ __('Pre-departure briefing and arrival guidance.') }}</p></a>
            </div>
        </div>
    </section>

    @endif

    @if($showSection('benefits'))
    <section class="sg-section sg-band" aria-labelledby="sg-steps-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <span class="sg-eyebrow">{{ __('How it works') }}</span>
                <h2 id="sg-steps-title">{{ __('Your journey to Singapore, step by step') }}</h2>
            </div>
            <ol class="sg-steps">
                <li><strong>{{ __('Free consultation') }}</strong>{{ __('Eligibility review and goals.') }}</li>
                <li><strong>{{ __('Course & institution') }}</strong>{{ __('Shortlist, intake and budget.') }}</li>
                <li><strong>{{ __('Admission') }}</strong>{{ __('Application and documents.') }}</li>
                <li><strong>{{ __('Student\'s Pass') }}</strong>{{ __('Preparation and tracking with ICA.') }}</li>
                <li><strong>{{ __('Departure & arrival') }}</strong>{{ __('Ticket, housing and arrival help.') }}</li>
            </ol>
            <p class="sg-note">{{ __('Admission decisions are made by the institution, and Student\'s Pass decisions by Singapore\'s Immigration & Checkpoints Authority (ICA).') }}</p>
        </div>
    </section>

    @endif

    @if($guides->isNotEmpty())
    <section class="sg-section sg-section-white" aria-labelledby="sg-guides-title">
        <div class="sg-shell sg-split">
            <div>
                <span class="sg-eyebrow">{{ __('Singapore Guides') }}</span>
                <h2 id="sg-guides-title">{{ __('Step-by-step, official-source guides') }}</h2>
                <p>{{ __('Checked against official Singapore sources such as ICA and MOE.') }}</p>
                <a href="{{ route('consultancy.guides.index') }}" class="sg-btn sg-btn-ghost-red">{{ __('View all guides') }} {!! $svg('arrow') !!}</a>
            </div>
            <div class="sg-guide-list">
                @foreach($guides as $guide)
                    <a class="sg-card sg-guide" href="{{ $guide->publicUrl() }}">
                        <img src="{{ $img($guideImages[$loop->index % count($guideImages)], 300) }}" alt="" width="96" height="68" loading="lazy" decoding="async">
                        <span><strong>{{ $guide->localized('title') }}</strong><span>{{ \Illuminate\Support\Str::limit((string) $guide->localized('summary'), 80) }}</span></span>
                        {!! $svg('arrow') !!}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($showSection('service_panels'))
    <section class="sg-section" aria-labelledby="sg-more-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <h2 id="sg-more-title">{{ __('Visa & Travel Services') }}</h2>
                <p>{{ __('Beyond study: visas, flights, hotels and tours from one team.') }}</p>
            </div>
            <div class="sg-grid sg-grid-3">
                @feature('visa')<a class="sg-card sg-feature sg-feature-left" href="{{ route('visa.index') }}"><span class="sg-icon sg-icon-navy">{!! $svg('visa') !!}</span><h3>{{ __('Visa Services') }}</h3><p>{{ __('Tourist, business and student visa guidance and document help.') }}</p><x-site.provider-badge :state="$serviceStatus('visa')" /></a>
                <a class="sg-card sg-feature sg-feature-left" href="{{ route('work-visa.index') }}"><span class="sg-icon sg-icon-navy">{!! $svg('briefcase') !!}</span><h3>{{ __('Work Visa') }}</h3><p>{{ __('Job visa and work permit information.') }}</p></a>@endfeature
                @if($flightsVisible)<a class="sg-card sg-feature sg-feature-left" href="{{ $flightsUrl }}"><span class="sg-icon">{!! $svg('plane') !!}</span><h3>{{ __('Flights') }}</h3><p>{{ __('Search fares or send a flight enquiry.') }}</p><x-site.provider-badge :state="$serviceStatus('flights')" /></a>@endif
                <a class="sg-card sg-feature sg-feature-left" href="{{ $serviceLink('hotels') ?? route('service-enquiries.create', 'hotel-booking') }}"><span class="sg-icon">{!! $svg('bed') !!}</span><h3>{{ __('Hotels & Homes') }}</h3><p>{{ __('Stays in Singapore, Bangladesh and abroad.') }}</p><x-site.provider-badge :state="$serviceStatus('hotels')" /></a>
                <a class="sg-card sg-feature sg-feature-left" href="{{ $serviceLink('tours') ?? route('service-enquiries.create', 'tour-package') }}"><span class="sg-icon sg-icon-green">{!! $svg('mountain') !!}</span><h3>{{ __('Tour Packages') }}</h3><p>{{ __('Holiday and group tour packages.') }}</p><x-site.provider-badge :state="$serviceStatus('tours')" /></a>
                @if($showSection('promotions'))@feature('about')<a class="sg-card sg-feature sg-feature-left" href="{{ route('offers.index') }}"><span class="sg-icon">{!! $svg('tag') !!}</span><h3>{{ __('Offers & Promotions') }}</h3><p>{{ __('Current offers from Eagle Global Hub.') }}</p></a>@endfeature @endif
            </div>
        </div>
    </section>

    @endif

    @if($showSection('destinations') && $destinations->isNotEmpty())
    <section class="sg-section sg-section-white" aria-labelledby="sg-bd-title">
        <div class="sg-shell">
            <div class="sg-section-head">
                <h2 id="sg-bd-title">{{ __('Explore Bangladesh') }}</h2>
                <p>{{ __('Discover the natural beauty, culture and heritage of Bangladesh.') }}</p>
            </div>
            <div class="sg-grid sg-grid-3">
                @foreach($destinations as $destination)
                    <a class="sg-photo-card" href="{{ route('explore-bangladesh.show', $destination->slug) }}">
                        @if($destination->cover_image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($destination->cover_image_path) }}" alt="{{ $destination->cover_image_alt ?? $destination->name }}" loading="lazy" decoding="async">@endif
                        <div><strong>{{ $destination->name }}</strong><span>{{ \Illuminate\Support\Str::limit((string) $destination->summary, 70) }}</span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if($showSection('newsletter'))
    <section class="sg-section-tight" aria-labelledby="sg-newsletter-title">
        <div class="sg-shell">
            <div class="sg-card sg-newsletter">
                <div><h2 id="sg-newsletter-title">{{ __((string) data_get($settings, 'newsletter_title', data_get($settings, 'journey_title'))) }}</h2><p>{{ __((string) data_get($settings, 'newsletter_subtitle', data_get($settings, 'journey_copy'))) }}</p></div>
                <form method="POST" action="{{ route('newsletter.subscribe') }}">@csrf<label class="sg-visually-hidden" for="newsletter-email">{{ __('Email address') }}</label><input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your email address') }}" autocomplete="email" required><button type="submit" class="sg-btn sg-btn-red">{{ __('Subscribe') }}</button></form>
            </div>
            @if(session('newsletter_status'))<p class="sg-note" role="status">{{ session('newsletter_status') }}</p>@endif
            @error('email')<p class="sg-note" role="alert">{{ $message }}</p>@enderror
        </div>
    </section>
    @endif
</main>
@endsection
