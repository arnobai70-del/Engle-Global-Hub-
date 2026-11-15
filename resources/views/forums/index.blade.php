@extends('layouts.site')
@section('title', __('Forums'))
@section('meta_description', __('Community discussions about study, travel and visas with Eagle Global Hub LTD.'))
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">{{ mb_strtoupper(__('Forums')) }}</span><h1>{{ __('Community forums') }}</h1><p>{{ __('Ask questions and share experiences. Do not post passport numbers, phone numbers or other personal details.') }}</p></div></section>
        @if($categories->isEmpty())
            <section class="egho-contact-card"><p>{{ __('No discussion areas are open yet.') }}</p></section>
        @else
            <div class="egho-contact-grid">
                @foreach($categories as $category)
                    <article class="egho-contact-card"><h2><a href="{{ route('forums.category', $category->slug) }}">{{ $category->name }}</a></h2><p>{{ $category->description }}</p></article>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
