{{--
    Hotel filter rail.

    The active hotel provider does not expose price, property-type or amenity
    filtering to this website, so the controls are rendered disabled instead of
    pretending to filter anything.
--}}
<aside class="egho-filter-card" aria-label="{{ __('Result filters') }}">
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
            <span>{{ __('BDT 150,000+') }}</span>
        </div>
    </div>

    <div class="egho-filter-group">
        <h2>{{ __('Property type') }}</h2>
        @foreach (['Apartment', 'Resort', 'Hotel', 'Villa'] as $type)
            <label class="egho-check">
                <input type="checkbox" disabled>
                <span>{{ $type }}</span>
            </label>
        @endforeach
    </div>

    <div class="egho-filter-group">
        <h2>{{ __('Amenities') }}</h2>
        @foreach (['Free wifi', 'Breakfast', 'Pool', 'Gym'] as $amenity)
            <label class="egho-check">
                <input type="checkbox" disabled>
                <span>{{ __($amenity) }}</span>
            </label>
        @endforeach
    </div>

    <p class="egho-filter-note">
        {{ __('The active hotel provider does not expose price, property-type or amenity filtering to this website yet, so these controls stay disabled. Rates shown by a provider are for the searched dates only.') }}
    </p>
</aside>
