@extends('layouts.site')
@section('title', __('Rewards'))
@section('meta_description', __('Eagle Global Hub LTD customer rewards programmes and terms.'))
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">{{ mb_strtoupper(__('Rewards')) }}</span><h1>{{ __('Rewards') }}</h1></div></section>
        @forelse($programs as $program)
            <section class="egho-contact-card">
                <h2>{{ $program->name }}</h2>
                @if($program->description)<p>{{ $program->description }}</p>@endif
                @auth<p><strong>{{ __('Your balance') }}: {{ number_format($balances[$program->id] ?? 0) }} {{ __('points') }}</strong></p>@else<p><a href="{{ route('login') }}">{{ __('Sign in') }}</a> {{ __('to see your points.') }}</p>@endauth
                <details><summary>{{ __('How to earn points') }}</summary><p style="white-space:pre-line">{{ $program->earning_rules }}</p></details>
                <details><summary>{{ __('Terms and conditions') }}</summary><p style="white-space:pre-line">{{ $program->terms }}</p></details>
            </section>
        @empty
            <section class="egho-contact-card"><p>{{ __('There is no active rewards programme at the moment.') }}</p></section>
        @endforelse
        @if($history->isNotEmpty())
            <section class="egho-contact-card"><h2>{{ __('Your points history') }}</h2><ul>@foreach($history as $entry)<li>{{ $entry->created_at->format('j M Y') }}: {{ $entry->points > 0 ? '+' : '' }}{{ $entry->points }} — {{ $entry->reason }}@if($entry->expires_at) ({{ __('expires') }} {{ $entry->expires_at->format('j M Y') }})@endif</li>@endforeach</ul></section>
        @endif
    </div>
</main>
@endsection
