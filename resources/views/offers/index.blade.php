@extends('layouts.site')
@section('title', 'Special Offers')
@section('meta_description', 'Current travel, visa and study consultancy offers from Eagle Global Hub LTD.')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">{{ __('SPECIAL OFFERS') }}</span><h1>{{ __('Special offers') }}</h1><p>{{ __('Current promotions from Eagle Global Hub LTD. Conditions apply as stated on each offer.') }}</p></div></section>
        @if($offers->isEmpty())
            <section class="egho-contact-card"><p>{{ __('There are no active offers right now. Please check back soon or') }} <a href="{{ route('support') }}">{{ __('contact our team') }}</a>.</p></section>
        @else
            <div class="egho-contact-grid">
                @foreach($offers as $offer)
                    <article class="egho-contact-card" id="{{ $offer->slug }}">
                        <h2>{{ $offer->title }}</h2>
                        @if($offer->service)<p class="egho-eyebrow">{{ strtoupper($offer->service->label()) }}</p>@endif
                        <p>{{ $offer->summary }}</p>
                        @if($offer->valid_until)<p><small>Valid until {{ $offer->valid_until->format('j M Y') }}</small></p>@endif
                        @if($offer->terms)<details><summary>{{ __('Terms and conditions') }}</summary><p style="white-space:pre-line">{{ $offer->terms }}</p></details>@endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
