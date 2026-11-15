@extends('layouts.site')
@section('title', $query !== '' ? __('Search').': '.$query : __('Search'))
@section('robots', 'noindex,follow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ __('Search') }}</h1>
            <form method="GET" action="{{ route('search') }}" role="search">
                <div class="egho-form-grid">
                    <label class="egho-field egho-field-wide"><span>{{ __('Search') }}</span><input type="search" name="q" value="{{ $query }}" maxlength="100" minlength="2" required></label>
                </div>
                <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Search') }}</button></div>
            </form>
            @if($query !== '' && mb_strlen($query) < 2)
                <p>{{ __('Please enter at least 2 characters.') }}</p>
            @elseif($query !== '')
                <h2>{{ __('Search results') }}</h2>
                @forelse($results as $result)
                    <article style="margin-bottom:14px"><small>{{ $result['type'] }}</small><h3 style="margin:2px 0"><a href="{{ $result['url'] }}">{{ $result['title'] }}</a></h3><p style="margin:0">{{ $result['summary'] }}</p></article>
                @empty
                    <p>{{ __('No results found.') }}</p>
                @endforelse
            @endif
        </section>
    </div>
</main>
@endsection
