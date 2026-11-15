@extends('layouts.site')
@section('title', $destination->meta_title ?: $destination->name.' — Explore Bangladesh')
@section('meta_description', $destination->meta_description ?: (string) $destination->summary)
@section('body_class', 'public-page-body egho-page-body')
@if($destination->cover_image_path)
@section('og_image', \Illuminate\Support\Facades\Storage::disk('public')->url($destination->cover_image_path))
@endif
@push('head')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        array_filter([
            '@type' => 'TouristDestination',
            'name' => $destination->name,
            'description' => $destination->summary,
            'url' => route('explore-bangladesh.show', $destination->slug),
            'image' => $destination->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($destination->cover_image_path) : null,
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
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <nav aria-label="Breadcrumb"><a href="{{ route('home') }}">{{ __('Home') }}</a> › <a href="{{ route('explore-bangladesh.index') }}">{{ __('Explore Bangladesh') }}</a> › <span aria-current="page">{{ $destination->name }}</span></nav>
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ strtoupper($destination->region ?: 'EXPLORE BANGLADESH') }}</span>
                <h1>{{ $destination->name }}</h1>
                <p>{{ $destination->summary }}</p>
            </div>
            @if($destination->cover_image_path)
                <div class="egho-contact-hero-media"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($destination->cover_image_path) }}" alt="{{ $destination->cover_image_alt ?? $destination->name }}" width="1400" height="933" style="height:auto"></div>
            @endif
        </section>
        @if($destination->description)
            <section class="egho-contact-card">
                @foreach(preg_split('/\R{2,}/', trim($destination->description)) as $paragraph)<p>{!! nl2br(e($paragraph)) !!}</p>@endforeach
            </section>
        @endif
        @if($destination->images->isNotEmpty())
            <section class="egho-contact-card" aria-label="Photo gallery">
                <div class="egho-contact-grid">
                    @foreach($destination->images as $image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" alt="{{ $image->alt_text }}" loading="lazy" style="width:100%;height:auto;border-radius:12px">@endforeach
                </div>
            </section>
        @endif
        @if($destination->recommendations->isNotEmpty())
            <section class="egho-contact-card" aria-labelledby="places-you-may-like">
                <h2 id="places-you-may-like">{{ __('Places You May Like') }}</h2>
                <div class="egho-contact-grid">
                    @foreach($destination->recommendations as $place)
                        <article><h3><a href="{{ route('explore-bangladesh.show', $place->slug) }}">{{ $place->name }}</a></h3><p>{{ $place->summary }}</p></article>
                    @endforeach
                </div>
            </section>
        @endif
        <p><a href="{{ route('support') }}" class="egho-btn egho-btn-primary">{{ __('Ask us about travelling here') }}</a></p>
    </div>
</main>
@endsection
