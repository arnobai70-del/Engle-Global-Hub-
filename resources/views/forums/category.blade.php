@extends('layouts.site')
@section('title', $category->name.' — '.__('Forums'))
@section('meta_description', (string) $category->description)
@section('body_class', 'public-page-body egho-page-body')
@if($category->visibility !== 'public')
@section('robots', 'noindex,nofollow')
@endif
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <nav aria-label="Breadcrumb"><a href="{{ route('forums.index') }}">{{ __('Forums') }}</a> › <span aria-current="page">{{ $category->name }}</span></nav>
        @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
        <section class="egho-contact-card">
            <h1>{{ $category->name }}</h1>
            @forelse($threads as $thread)
                <article style="margin-bottom:12px"><h2 style="margin:0;font-size:1.05rem"><a href="{{ route('forums.thread', [$category->slug, $thread->slug]) }}">{{ $thread->is_pinned ? '📌 ' : '' }}{{ $thread->title }}</a></h2><small>{{ $thread->user?->name }} · {{ $thread->last_posted_at?->format('j M Y') }}@if($thread->is_locked) · {{ __('Closed') }}@endif</small></article>
            @empty
                <p>{{ __('No topics yet.') }}</p>
            @endforelse
            {{ $threads->links() }}
        </section>
        <section class="egho-contact-card">
            <h2>{{ __('Start a new topic') }}</h2>
            @auth
                @if($errors->any())<div class="egho-result-summary" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                <form method="POST" action="{{ route('forums.threads.store', $category->slug) }}">@csrf
                    <div class="egho-form-grid">
                        <label class="egho-field egho-field-wide"><span>{{ __('Title') }}</span><input name="title" value="{{ old('title') }}" minlength="8" maxlength="150" required></label>
                        <label class="egho-field egho-field-wide"><span>{{ __('Message') }}</span><textarea class="egho-textarea" name="body" minlength="20" maxlength="5000" required>{{ old('body') }}</textarea></label>
                        <div hidden aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
                    </div>
                    <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Post topic') }}</button><small>{{ __('Personal details such as emails and phone numbers are removed automatically.') }}</small></div>
                </form>
            @else
                <p><a href="{{ route('login') }}">{{ __('Sign in') }}</a> {{ __('to start a topic.') }}</p>
            @endauth
        </section>
    </div>
</main>
@endsection
