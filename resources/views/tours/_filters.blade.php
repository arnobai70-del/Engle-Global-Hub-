{{--
    Tour filter rail.

    The active tour provider does not expose category, price, duration or
    rating filtering to this website, so the controls are rendered disabled
    instead of pretending to filter anything.
--}}
<aside class="egho-filter-card" aria-label="{{ __('Result filters') }}">
    <div class="egho-filter-group">
        <h2>{{ __('Category') }}</h2>
        @foreach (['Walking tours', 'Free entry', 'Desert safari'] as $category)
            <label class="egho-check">
                <input type="checkbox" disabled>
                <span>{{ __($category) }}</span>
            </label>
        @endforeach
    </div>

    <div class="egho-filter-group">
        <h2>{{ __('Price range') }}</h2>
        <input
            class="egho-range"
            type="range"
            min="0"
            max="100"
            value="60"
            aria-label="{{ __('Price range') }}"
            disabled
        >
        <div class="egho-range-row">
            <span>{{ __('BDT 0') }}</span>
            <span>{{ __('BDT 50,000+') }}</span>
        </div>
    </div>

    <div class="egho-filter-group">
        <h2>{{ __('Duration') }}</h2>
        @foreach (['1-2 hours', '3 hours', '5 hours', '7+ hours'] as $duration)
            <label class="egho-check">
                <input type="checkbox" disabled>
                <span>{{ __($duration) }}</span>
            </label>
        @endforeach
    </div>

    <div class="egho-filter-group">
        <h2>{{ __('Rating') }}</h2>
        @foreach (['Top rated', '5 stars', '4 stars'] as $rating)
            <label class="egho-check">
                <input type="checkbox" disabled>
                <span>{{ $rating }}</span>
            </label>
        @endforeach
    </div>

    <p class="egho-filter-note">
        {{ __('The active tour provider does not expose category, price, duration or rating filtering to this website yet, so these controls stay disabled.') }}
    </p>
</aside>
