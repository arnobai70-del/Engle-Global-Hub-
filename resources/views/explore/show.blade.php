@extends('layouts.site')

@php
    $storage = \Illuminate\Support\Facades\Storage::disk('public');
    $name = (string) $destination->localized('name');
    $summary = $destination->localized('summary');
    $description = $destination->localized('description');
    $cover = $destination->cover_image_path ? $storage->url($destination->cover_image_path) : null;
    $facts = app(\App\Services\SiteFacts::class);
    $tripWhatsapp = $facts->whatsappUrl('Hello Eagle Global Hub, I am planning a trip to Bangladesh ('.$destination->name.') and would like help with ...');
    $paragraphs = static fn (?string $text): array => $text !== null && trim($text) !== '' ? (preg_split('/\R{2,}/', trim($text)) ?: []) : [];
    $mapUrl = $destination->hasCoordinates() ? 'https://www.openstreetmap.org/?mlat='.$destination->latitude.'&mlon='.$destination->longitude.'#map=12/'.$destination->latitude.'/'.$destination->longitude : null;
    $mapEmbed = $destination->hasCoordinates()
        ? 'https://www.openstreetmap.org/export/embed.html?bbox='.($destination->longitude - 0.08).'%2C'.($destination->latitude - 0.05).'%2C'.($destination->longitude + 0.08).'%2C'.($destination->latitude + 0.05).'&layer=mapnik&marker='.$destination->latitude.'%2C'.$destination->longitude
        : null;
@endphp

@section('title', $destination->meta_title ?: __(':place: travel guide, where to stay and tours', ['place' => $name]))
@section('meta_description', $destination->meta_description ?: ($summary ?: __('Travel guide to :place in Bangladesh, with help planning your hotel, transport and guide.', ['place' => $name])))
@section('body_class', 'sg-body-v2')
@if($cover)
@section('og_image', $cover)
@endif

@push('head')
    @if($cover)<link rel="preload" as="image" href="{{ $cover }}" fetchpriority="high">@endif
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'TouristAttraction',
                'name' => $destination->name,
                'description' => $destination->summary,
                'url' => route('explore-bangladesh.show', $destination->slug),
                'image' => $cover,
                'address' => array_filter(['@type' => 'PostalAddress', 'addressRegion' => $destination->region, 'addressCountry' => 'BD']),
                'geo' => $destination->hasCoordinates() ? ['@type' => 'GeoCoordinates', 'latitude' => $destination->latitude, 'longitude' => $destination->longitude] : null,
                'isAccessibleForFree' => null,
            ]),
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Explore Bangladesh', 'item' => route('explore-bangladesh.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $destination->name, 'item' => route('explore-bangladesh.show', $destination->slug)],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('footer_cta')
    <div class="sg-footer-cta">
        <div><h2>{{ __('Planning a trip to Bangladesh?') }}</h2><p>{{ __('Talk to our travel team about hotels, transport, guides and tours.') }}</p></div>
        <div class="sg-footer-cta-actions"><a href="#plan-trip" class="sg-btn sg-btn-red">{{ __('Plan my trip') }}</a>@if($tripWhatsapp)<a href="{{ $tripWhatsapp }}" class="sg-btn sg-btn-whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>@endif</div>
    </div>
@endsection

@section('content')
<main>
    <section class="v2-explore-hero {{ $cover ? 'has-photo' : '' }}" @if($cover) style="--explore-hero: url('{{ $cover }}')" @endif aria-labelledby="destination-title">
        <div class="v2-shell">
            <nav class="sg-crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a> › <span aria-current="page">{{ $name }}</span></nav>
            <div class="v2-explore-card-meta">
                @if($destination->categoryLabel())<a class="v2-pill v2-pill-light" href="{{ route('explore-bangladesh.index', ['category' => $destination->category]) }}">{{ $destination->categoryLabel() }}</a>@endif
                @if($destination->region)<span class="v2-pill v2-pill-light">{{ __($destination->region) }}</span>@endif
            </div>
            <h1 id="destination-title">{{ $name }}</h1>
            @if($summary)<p>{{ $summary }}</p>@endif
            <div class="v2-actions">
                <a class="v2-btn v2-btn-red" href="#plan-trip">{{ __('Plan a trip to :place', ['place' => $name]) }}</a>
                @if($tripWhatsapp)<a class="v2-btn v2-btn-ghost-light" href="{{ $tripWhatsapp }}" target="_blank" rel="noopener noreferrer">{{ __('Chat on WhatsApp') }}</a>@endif
            </div>
            @if($cover && $destination->cover_image_alt)<p class="v2-explore-credit">{{ $destination->cover_image_alt }}</p>@endif
        </div>
    </section>

    <section class="v2-section" aria-label="{{ __('About this place') }}">
        <div class="v2-shell v2-explore-detail">
            <div class="v2-explore-text">
                @foreach($paragraphs($description) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach

                @if($destination->images->isNotEmpty())
                    <h2 class="v2-h3">{{ __('Photos') }}</h2>
                    <div class="v2-explore-gallery">
                        @foreach($destination->images as $image)<img src="{{ $storage->url($image->path) }}" alt="{{ $image->alt_text }}" loading="lazy" decoding="async" width="640" height="420">@endforeach
                    </div>
                @endif

                @if($destination->how_to_get_there)
                    <h2 class="v2-h3">{{ __('How to get there') }}</h2>
                    @foreach($paragraphs($destination->how_to_get_there) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
                @endif
                @if($destination->travel_tips)
                    <h2 class="v2-h3">{{ __('Travel tips') }}</h2>
                    @foreach($paragraphs($destination->travel_tips) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
                @endif
            </div>
            <aside class="v2-card v2-explore-facts" aria-label="{{ __('At a glance') }}">
                <h2 class="v2-h3">{{ __('At a glance') }}</h2>
                <dl>
                    @if($destination->region)<div><dt>{{ __('Region') }}</dt><dd>{{ __($destination->region) }}</dd></div>@endif
                    @if($destination->categoryLabel())<div><dt>{{ __('Category') }}</dt><dd>{{ $destination->categoryLabel() }}</dd></div>@endif
                    @if($destination->best_time_to_visit)<div><dt>{{ __('Best time to visit') }}</dt><dd>{{ $destination->best_time_to_visit }}</dd></div>@endif
                </dl>
                @if($mapUrl)<a class="v2-link" href="{{ $mapUrl }}" target="_blank" rel="noopener nofollow">{{ __('View on map') }} →</a>@endif
            </aside>
        </div>
    </section>

    @foreach(\App\Models\DestinationListing::TYPES as $type => $heading)
        @if(($listings[$type] ?? collect())->isNotEmpty())
        <section class="v2-section v2-section-white v2-explore-listings" aria-labelledby="listings-{{ $type }}">
            <div class="v2-shell">
                <h2 id="listings-{{ $type }}" class="v2-h2">{{ __($heading) }}</h2>
                <div class="v2-explore-grid">
                    @foreach($listings[$type] as $listing)
                        <article class="v2-card v2-explore-listing">
                            <div class="v2-explore-card-meta">
                                @if($listing->area)<small>{{ $listing->area }}</small>@endif
                                @if($listing->priceLabel())<span class="v2-pill v2-pill-enquiry">{{ $listing->priceLabel() }}</span>@endif
                            </div>
                            <h3>{{ $listing->name }}</h3>
                            @if($listing->description)<p>{{ $listing->description }}</p>@endif
                            <div class="v2-explore-links">
                                @if($listing->website_url)<a class="v2-link" href="{{ $listing->website_url }}" target="_blank" rel="noopener nofollow">{{ __('Website') }} ↗</a>@endif
                                @if($listing->map_url)<a class="v2-link" href="{{ $listing->map_url }}" target="_blank" rel="noopener nofollow">{{ __('View on map') }} ↗</a>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    @endforeach

    @if($mapEmbed)
    <section class="v2-section" aria-labelledby="map-title">
        <div class="v2-shell">
            <h2 id="map-title" class="v2-h2">{{ __('Map') }}</h2>
            <iframe class="v2-explore-map" src="{{ $mapEmbed }}" title="{{ __('Map of :place', ['place' => $name]) }}" loading="lazy" referrerpolicy="no-referrer"></iframe>
        </div>
    </section>
    @endif

    @if($destination->recommendations->isNotEmpty())
    <section class="v2-section v2-section-white" aria-labelledby="places-you-may-like">
        <div class="v2-shell">
            <h2 id="places-you-may-like" class="v2-h2">{{ __('Places You May Like') }}</h2>
            <div class="v2-explore-grid">
                @foreach($destination->recommendations as $place)
                    <article class="v2-explore-card">
                        <a class="v2-explore-card-media" href="{{ route('explore-bangladesh.show', $place->slug) }}" tabindex="-1" aria-hidden="true">
                            @if($place->cover_image_path)<img src="{{ $storage->url($place->cover_image_path) }}" alt="{{ $place->cover_image_alt ?: $place->localized('name') }}" loading="lazy" decoding="async" width="640" height="420">@else<span class="v2-explore-card-placeholder"></span>@endif
                        </a>
                        <div class="v2-explore-card-body">
                            <h3><a href="{{ route('explore-bangladesh.show', $place->slug) }}">{{ $place->localized('name') }}</a></h3>
                            @if($place->localized('summary'))<p>{{ $place->localized('summary') }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="v2-section" id="plan-trip" aria-label="{{ __('Plan a trip to :place', ['place' => $name]) }}">
        <div class="v2-shell v2-explore-plan">
            <div>
                <span class="v2-eyebrow">{{ __('Plan my trip') }}</span>
                <h2 class="v2-h2">{{ __('We arrange your visit to :place', ['place' => $name]) }}</h2>
                <p>{{ __('Hotel, transport, a guide and the route from Dhaka. Tell us your dates and our travel team replies with a plan and a written quote.') }}</p>
                @if($tripWhatsapp)<div class="v2-actions"><a class="v2-btn v2-btn-wa" href="{{ $tripWhatsapp }}" target="_blank" rel="noopener noreferrer">{{ __('Chat on WhatsApp') }}</a></div>@endif
            </div>
            @include('partials.explore.trip-form', ['title' => __('Plan a trip to :place', ['place' => $name]), 'selected' => [$destination->id]])
        </div>
    </section>
</main>
@endsection
