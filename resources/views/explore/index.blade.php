@extends('layouts.site')

@php
    $storage = \Illuminate\Support\Facades\Storage::disk('public');
    $facts = app(\App\Services\SiteFacts::class);
    $tripWhatsapp = $facts->whatsappUrl('Hello Eagle Global Hub, I am planning a trip to Bangladesh and would like help with ...');
    $phone = $facts->phoneDisplay();
    $categoryTitle = $category !== null ? __(\App\Models\Destination::CATEGORIES[$category]) : null;
    $heroImage = $heroDestination ? $storage->url($heroDestination->cover_image_path) : null;
@endphp

@section('title', $categoryTitle ? __('Explore Bangladesh: :category', ['category' => $categoryTitle]) : __('Explore Bangladesh: places to visit, travel tips and trip planning'))
@section('meta_description', __('Historical sites, beaches, hills and forests across Bangladesh for foreign visitors, with travel essentials and help planning your hotel, transport, guide and tour.'))
@section('body_class', 'sg-body-v2')
@if($category !== null || $destinations->currentPage() > 1)
@section('canonical', route('explore-bangladesh.index'))
@section('robots', 'noindex,follow')
@endif
@if($heroImage)
@section('og_image', $heroImage)
@endif

@push('head')
    @if($heroImage)<link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">@endif
    @if($destinations->isNotEmpty())
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'ItemList',
                'name' => 'Explore Bangladesh',
                'itemListElement' => $destinations->values()->map(fn ($destination, $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => array_filter([
                        '@type' => 'TouristDestination',
                        'name' => $destination->name,
                        'description' => $destination->summary,
                        'url' => route('explore-bangladesh.show', $destination->slug),
                        'image' => $destination->cover_image_path ? $storage->url($destination->cover_image_path) : null,
                    ]),
                ])->all(),
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Explore Bangladesh', 'item' => route('explore-bangladesh.index')],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')
<main>
    <section class="v2-explore-hero {{ $heroImage ? 'has-photo' : '' }}" @if($heroImage) style="--explore-hero: url('{{ $heroImage }}')" @endif aria-labelledby="explore-title">
        <div class="v2-shell">
            <nav class="sg-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <span aria-current="page">{{ __('Explore Bangladesh') }}</span></nav>
            <span class="v2-pill v2-pill-light">{{ __('For international travellers') }}</span>
            <h1 id="explore-title">{{ __('Explore Bangladesh') }}</h1>
            <p>{{ __('UNESCO heritage sites, the longest sea beach, tea hills and the Sundarbans mangrove forest. Find places to visit, read the travel essentials, and let our team arrange your hotel, transport, guide and tour.') }}</p>
            <div class="v2-actions">
                <a class="v2-btn v2-btn-red" href="#plan-trip">{{ __('Plan my trip') }}</a>
                @if($tripWhatsapp)<a class="v2-btn v2-btn-ghost-light" href="{{ $tripWhatsapp }}" target="_blank" rel="noopener noreferrer">{{ __('Chat on WhatsApp') }}</a>@endif
            </div>
            @if($heroDestination && $heroDestination->cover_image_alt)<p class="v2-explore-credit">{{ __('Photo: :place', ['place' => $heroDestination->cover_image_alt]) }}</p>@endif
        </div>
    </section>

    <section class="v2-section" aria-labelledby="places-title">
        <div class="v2-shell">
            <div class="v2-head">
                <div><span class="v2-eyebrow">{{ __('Places to visit') }}</span><h2 id="places-title" class="v2-h2">{{ $categoryTitle ?? __('Destinations across Bangladesh') }}</h2></div>
            </div>
            <nav class="v2-explore-filter" aria-label="{{ __('Filter by category') }}">
                <a href="{{ route('explore-bangladesh.index') }}" @if($category === null) aria-current="page" @endif>{{ __('All') }}</a>
                @foreach(\App\Models\Destination::CATEGORIES as $value => $label)
                    <a href="{{ route('explore-bangladesh.index', ['category' => $value]) }}" @if($category === $value) aria-current="page" @endif>{{ __($label) }}</a>
                @endforeach
            </nav>

            @if($destinations->isNotEmpty())
                <div class="v2-explore-grid">
                    @foreach($destinations as $destination)
                        <article class="v2-explore-card">
                            <a class="v2-explore-card-media" href="{{ route('explore-bangladesh.show', $destination->slug) }}" tabindex="-1" aria-hidden="true">
                                @if($destination->cover_image_path)
                                    <img src="{{ $storage->url($destination->cover_image_path) }}" alt="{{ $destination->cover_image_alt ?: $destination->localized('name') }}" loading="lazy" decoding="async" width="640" height="420">
                                @else
                                    <span class="v2-explore-card-placeholder"></span>
                                @endif
                            </a>
                            <div class="v2-explore-card-body">
                                <div class="v2-explore-card-meta">
                                    @if($destination->categoryLabel())<span class="v2-pill v2-pill-enquiry">{{ $destination->categoryLabel() }}</span>@endif
                                    @if($destination->region)<small>{{ __($destination->region) }}</small>@endif
                                </div>
                                <h3><a href="{{ route('explore-bangladesh.show', $destination->slug) }}">{{ $destination->localized('name') }}</a></h3>
                                @if($destination->localized('summary'))<p>{{ $destination->localized('summary') }}</p>@endif
                                <a class="v2-link" href="{{ route('explore-bangladesh.show', $destination->slug) }}" aria-label="{{ __('Explore :place', ['place' => $destination->localized('name')]) }}">{{ __('Explore') }} →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if($destinations->hasPages())<div class="v2-pagination">{{ $destinations->links() }}</div>@endif
            @else
                <div class="v2-card v2-empty">
                    <p>{{ $category !== null ? __('No destination in this category is published yet. See all destinations, or tell us what you would like to see and we will plan it with you.') : __('Our destination guides are being prepared. Tell us what you would like to see and our travel team will plan the trip with you.') }}</p>
                    <a class="v2-btn v2-btn-red" href="#plan-trip">{{ __('Plan my trip') }}</a>
                </div>
            @endif
        </div>
    </section>

    <section class="v2-section v2-section-white" id="essentials" aria-labelledby="essentials-title">
        <div class="v2-shell">
            <div class="v2-head-center"><span class="v2-eyebrow">{{ __('Before you go') }}</span><h2 id="essentials-title" class="v2-h2">{{ __('Travel essentials for foreigners') }}</h2></div>
            <div class="v2-explore-essentials">
                @foreach($essentials as $item)
                    <div class="v2-card"><h3>{{ $item['title'] }}</h3>@if($item['text'] !== '')<p>{{ $item['text'] }}</p>@endif</div>
                @endforeach
            </div>
            <p class="v2-explore-visa">
                {{ __('Visa rules depend on your nationality and change from time to time. Always check the official Bangladesh e-visa and visa-on-arrival information before you travel.') }}
                @if(\Illuminate\Support\Facades\Route::has('visa.index'))<a class="v2-link" href="{{ route('visa.index') }}">{{ __('Our visa help') }} →</a>@endif
            </p>
        </div>
    </section>

    <section class="v2-section" id="plan-trip" aria-labelledby="plan-trip-title">
        <div class="v2-shell v2-explore-plan">
            <div>
                <span class="v2-eyebrow">{{ __('Plan my trip') }}</span>
                <h2 id="plan-trip-title" class="v2-h2">{{ __('We arrange your trip in Bangladesh') }}</h2>
                <p>{{ __('Hotels, airport pickup, a car with driver, an English-speaking guide and domestic flights. Tell us where you want to go and when; our team in Dhaka replies with a plan and a written quote.') }}</p>
                <ul class="v2-ticks">
                    <li>{{ __('One contact person before and during your trip') }}</li>
                    <li>{{ __('Written quote before you pay anything') }}</li>
                    <li>{{ __('English and Bangla support') }}</li>
                </ul>
                <div class="v2-actions">
                    @if($tripWhatsapp)<a class="v2-btn v2-btn-wa" href="{{ $tripWhatsapp }}" target="_blank" rel="noopener noreferrer">{{ __('Chat on WhatsApp') }}</a>@endif
                    @if($phone)<a class="v2-btn v2-btn-outline" href="{{ $facts->telUrl() }}">{{ __('Call :phone', ['phone' => $phone]) }}</a>@endif
                </div>
            </div>
            @include('partials.explore.trip-form', ['title' => __('Plan my trip'), 'selected' => []])
        </div>
    </section>
</main>
@endsection
