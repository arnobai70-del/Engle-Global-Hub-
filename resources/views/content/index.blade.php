@extends('layouts.site')
@section('title', $heading)
@section('meta_description', $intro)
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">{{ mb_strtoupper($heading) }}</span><h1>{{ $heading }}</h1><p>{{ $intro }}</p></div></section>
        @if($entries->isEmpty())
            <section class="egho-contact-card"><p>{{ __('Nothing has been published here yet. Please check back soon or contact our team.') }}</p></section>
        @else
            <div class="egho-contact-grid">
                @foreach($entries as $entry)
                    <article class="egho-contact-card">
                        @if($entry->cover)<img src="{{ $entry->cover->url() }}" alt="{{ $entry->cover->alt_text }}" loading="lazy" @if($entry->cover->width) width="{{ $entry->cover->width }}" height="{{ $entry->cover->height }}" @endif style="width:100%;height:auto;border-radius:12px">@endif
                        <h2><a href="{{ $entry->publicUrl() }}">{{ $entry->localized('title') }}</a></h2>
                        @if($entry->category || $entry->location)<p class="egho-eyebrow">{{ mb_strtoupper(trim($entry->category.' '.($entry->location ? '· '.$entry->location : ''), ' ·')) }}</p>@endif
                        <p>{{ $entry->localized('summary') }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
