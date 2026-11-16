@extends('layouts.site')

@section('title', __('Map'))
@section('meta_description', __('Places featured by Eagle Global Hub LTD with map links. The interactive map will be added when a map provider is connected.'))
@section('body_class', 'public-page-body egho-page-body')

@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('EXPLORE BANGLADESH') }}</span>
                <h1>{{ __('Map') }}</h1>
                <p>{{ __('Places from our guides and listings. Each place opens on OpenStreetMap; an interactive map on this page needs a map provider.') }}</p>
            </div>
        </section>

        <nav aria-label="{{ __('Breadcrumb') }}" class="sg-breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a> › <span aria-current="page">{{ __('Map') }}</span></nav>

        <x-site.api-notice capability="maps" />

        <section class="sg-section-tight" aria-labelledby="map-places">
            <h2 id="map-places" class="sg-related-title">{{ __('Places with a location') }}</h2>
            @if($places->isEmpty())
                <div class="sg-card sg-empty"><h2>{{ __('No places are published yet') }}</h2><p>{{ __('Restaurants, guides and articles with a location will appear here once they are published.') }}</p><a class="sg-btn sg-btn-red" href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a></div>
            @else
                <div class="sg-grid sg-grid-3">
                    @foreach($places as $place)
                        <article class="sg-card sg-feature sg-feature-left">
                            <span class="sg-icon"><x-site.icon name="map" /></span>
                            <h3>{{ $place->localized('title') }}</h3>
                            @if($place->location)<p>{{ $place->location }}</p>@endif
                            <a class="sg-link-arrow" href="https://www.openstreetmap.org/?mlat={{ $place->latitude }}&amp;mlon={{ $place->longitude }}#map=16/{{ $place->latitude }}/{{ $place->longitude }}" target="_blank" rel="noopener noreferrer">{{ __('View on map') }}</a>
                            @if($place->publicUrl())<a class="sg-link-arrow" href="{{ $place->publicUrl() }}">{{ __('Details') }}</a>@endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if($destinations->isNotEmpty())
        <section class="sg-section-tight" aria-labelledby="map-destinations">
            <h2 id="map-destinations" class="sg-related-title">{{ __('Tourist places in Bangladesh') }}</h2>
            <ul class="sg-inline-links">@foreach($destinations as $destination)<li><a href="{{ route('explore-bangladesh.show', $destination->slug) }}">{{ $destination->name }}</a></li>@endforeach</ul>
        </section>
        @endif
    </div>
</main>
@endsection
