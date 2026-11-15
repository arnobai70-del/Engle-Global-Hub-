@extends('layouts.site')
@section('title', $thread->title.' — '.__('Forums'))
@section('meta_description', \Illuminate\Support\Str::limit((string) $posts->first()?->body, 160))
@section('body_class', 'public-page-body egho-page-body')
@if($category->visibility !== 'public')
@section('robots', 'noindex,nofollow')
@endif
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <nav aria-label="Breadcrumb"><a href="{{ route('forums.index') }}">{{ __('Forums') }}</a> › <a href="{{ route('forums.category', $category->slug) }}">{{ $category->name }}</a></nav>
        @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
        @if($errors->any())<div class="egho-result-summary" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <section class="egho-contact-card">
            <h1>{{ $thread->title }}</h1>
            @foreach($posts as $post)
                <article id="post-{{ $post->id }}" style="border-top:1px solid rgba(0,0,0,.08);padding:12px 0">
                    <strong>{{ $post->user?->name ?? __('Former member') }}</strong> <small>{{ $post->created_at->format('j M Y, H:i') }}</small>
                    <p style="white-space:pre-line;margin:6px 0">{{ $post->body }}</p>
                    @auth<details><summary><small>{{ __('Report this post') }}</small></summary><form method="POST" action="{{ route('forums.posts.report', $post) }}">@csrf<label class="egho-field"><span>{{ __('Reason') }}</span><input name="reason" maxlength="500" required></label><button type="submit" class="egho-btn">{{ __('Send report') }}</button></form></details>@endauth
                </article>
            @endforeach
            {{ $posts->links() }}
        </section>
        <section class="egho-contact-card">
            @if($thread->is_locked)
                <p>{{ __('This topic is closed for replies.') }}</p>
            @else
                @auth
                    <form method="POST" action="{{ route('forums.replies.store', [$category->slug, $thread->slug]) }}">@csrf
                        <div class="egho-form-grid"><label class="egho-field egho-field-wide"><span>{{ __('Your reply') }}</span><textarea class="egho-textarea" name="body" minlength="5" maxlength="5000" required></textarea></label><div hidden aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div></div>
                        <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Post reply') }}</button></div>
                    </form>
                @else
                    <p><a href="{{ route('login') }}">{{ __('Sign in') }}</a> {{ __('to reply.') }}</p>
                @endauth
            @endif
        </section>
    </div>
</main>
@endsection
