@extends('layouts.site')
@section('title', 'Explore Bangladesh')
@section('meta_description', 'Discover tourist places in Bangladesh with Eagle Global Hub LTD, including Cox\'s Bazar, Sylhet and Kuakata.')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero">
            <div>
                <span class="egho-eyebrow">{{ __('EXPLORE BANGLADESH') }}</span>
                <h1>{{ __('Explore Bangladesh') }}</h1>
                <p>{{ __('Tourist places across Bangladesh, with travel support from Eagle Global Hub LTD.') }}</p>
            </div>
        </section>
        @if($destinations->isEmpty())
            <section class="egho-contact-card"><p>{{ __('Destination guides are being prepared and will be published soon. For travel help now, please') }} <a href="{{ route('support') }}">{{ __('contact our team') }}</a>.</p></section>
        @else
            <div class="egho-contact-grid">
                @foreach($destinations as $destination)
                    <article class="egho-contact-card">
                        @if($destination->cover_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($destination->cover_image_path) }}" alt="{{ $destination->cover_image_alt ?? $destination->name }}" loading="lazy" style="width:100%;height:auto;border-radius:12px">
                        @endif
                        <h2><a href="{{ route('explore-bangladesh.show', $destination->slug) }}">{{ $destination->name }}</a></h2>
                        @if($destination->region)<p class="egho-eyebrow">{{ $destination->region }}</p>@endif
                        <p>{{ $destination->summary }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
