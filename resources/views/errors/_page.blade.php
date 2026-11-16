<main class="egho-page sg-error-page">
    <div class="egho-shell">
        <section class="sg-card sg-error-card" role="alert" aria-labelledby="error-title">
            <span class="sg-error-code">{{ $code }}</span>
            <h1 id="error-title">{{ $title }}</h1>
            <p>{{ $message }}</p>
            <div class="sg-cta-actions">
                <a href="{{ route('home') }}" class="sg-btn sg-btn-red">{{ __('Home') }}</a>
                @if(! empty($back))<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="sg-btn sg-btn-outline">{{ __('Go back') }}</a>@endif
                @feature('support')<a href="{{ route('support') }}" class="sg-btn sg-btn-outline">{{ __('Contact Us') }}</a>@endfeature
                @guest<a href="{{ route('login') }}" class="sg-btn sg-btn-outline">{{ __('Login') }}</a>@endguest
            </div>
        </section>
    </div>
</main>
