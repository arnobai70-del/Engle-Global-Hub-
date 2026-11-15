@extends('layouts.site')
@section('title', 'Customer Reviews')
@section('meta_description', 'Reviews from Eagle Global Hub LTD customers, published after moderation.')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-hero"><div><span class="egho-eyebrow">{{ __('REVIEWS') }}</span><h1>{{ __('Customer reviews') }}</h1><p>{{ __('Written by signed-in customers and published after our team checks them. We do not edit review wording.') }}</p></div></section>
        @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
        <div class="egho-contact-grid">
            <section class="egho-contact-card" aria-labelledby="published-reviews">
                <h2 id="published-reviews">{{ __('What customers say') }}</h2>
                @forelse($reviews as $review)
                    <article style="margin-bottom:16px">
                        <strong>{{ $review->title }}</strong> — <span aria-label="Rated {{ $review->rating }} out of 5">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        <p style="white-space:pre-line;margin:4px 0">{{ $review->body }}</p>
                        <small>{{ $review->display_name }}@if($review->service) · {{ $review->service->label() }}@endif</small>
                    </article>
                @empty
                    <p>{{ __('No reviews have been published yet.') }}</p>
                @endforelse
                {{ $reviews->links() }}
            </section>
            <section class="egho-contact-card" aria-labelledby="write-review">
                <h2 id="write-review">{{ __('Write a review') }}</h2>
                @auth
                    @if($errors->any())<div class="egho-result-summary" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <form method="POST" action="{{ route('reviews.store') }}">
                        @csrf
                        <div class="egho-form-grid">
                            <label class="egho-field"><span>{{ __('Name to display') }}</span><input name="display_name" value="{{ old('display_name', auth()->user()->name) }}" maxlength="80" required></label>
                            <label class="egho-field"><span>{{ __('Rating') }}</span><select name="rating" required>@for($i = 5; $i >= 1; $i--)<option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} / 5</option>@endfor</select></label>
                            <label class="egho-field"><span>{{ __('Service used') }}</span><select name="service"><option value="">{{ __('General') }}</option>@foreach($services as $service)<option value="{{ $service->value }}" @selected(old('service') === $service->value)>{{ $service->label() }}</option>@endforeach</select></label>
                            <label class="egho-field"><span>{{ __('Title') }}</span><input name="title" value="{{ old('title') }}" maxlength="120" required></label>
                            <label class="egho-field egho-field-wide"><span>{{ __('Your review') }}</span><textarea class="egho-textarea" name="body" minlength="20" maxlength="3000" required>{{ old('body') }}</textarea></label>
                        </div>
                        <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Submit review') }}</button></div>
                    </form>
                @else
                    <p><a href="{{ route('login') }}">{{ __('Sign in') }}</a> {{ __('to write a review about a service you used.') }}</p>
                @endauth
            </section>
        </div>
    </div>
</main>
@endsection
