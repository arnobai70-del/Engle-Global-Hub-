{{--
    Our Services grid and the Popular Destinations rail.

    A tile that maps to a travel service carries that service's own status chip
    (Available / Not Configured) from the registry, and is a link only when the
    registry reports it available and the account holds its permission. A tile
    for a product this application does not offer says so instead of linking
    nowhere.
--}}
<section class="egho-section egho-section-alt">
    <div class="egho-shell">

        <div class="egho-section-head">
            <div>
                <span class="egho-eyebrow">Everything you need</span>
                <h2>Our Services</h2>
                <p>
                    Everything you need for a perfect journey. A service is
                    listed as available only when it is enabled and its
                    provider configuration is complete.
                </p>
            </div>
        </div>

        <div class="egho-tiles">

            @foreach ($serviceTiles as $tile)
                {{--
                    Only tiles that map to a real feature are gated: a product
                    this website does not sell has no feature flag to be hidden
                    with, and is rendered by the second loop below.
                --}}
                @continue(! $tile['service'] && $tile['key'] !== 'work-visa')

                @feature($tile['key'] === 'work-visa' ? 'visa' : $tile['key'])
                    @php
                        $tileLink = $tile['key'] === 'work-visa'
                            ? route('work-visa.index')
                            : $serviceLink($tile['key']);

                        $tileStatus = $tile['service']
                            ? $serviceStatus($tile['service'])
                            : null;
                    @endphp

                    @if ($tileLink)
                        <a href="{{ $tileLink }}" class="egho-tile">
                            <span class="egho-tile-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">{!! $tile['icon'] !!}</svg>
                            </span>

                            <span>
                                <strong>{{ $tile['title'] }}</strong>
                                <small>{{ $tile['copy'] }}</small>

                                @if ($tileStatus)
                                    <span @class([
                                        'egho-tile-status',
                                        'is-live' => $tileStatus['live'],
                                    ])>
                                        {{ $tileStatus['label'] }}
                                    </span>
                                @endif
                            </span>
                        </a>
                    @else
                        <span class="egho-tile">
                            <span class="egho-tile-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24">{!! $tile['icon'] !!}</svg>
                            </span>

                            <span>
                                <strong>{{ $tile['title'] }}</strong>
                                <small>{{ $tile['copy'] }}</small>

                                @if ($tileStatus)
                                    {{-- Registry service, not available yet. --}}
                                    <span class="egho-tile-status">
                                        {{ $tileStatus['label'] }}
                                    </span>
                                @endif
                            </span>
                        </span>
                    @endif
                @endfeature
            @endforeach

            {{--
                Products this website does not sell yet.

                The panel shows ten tiles; five of them are services with no
                feature flag and no route behind them. They are rendered as
                tiles that say so, so the grid matches the panel without a
                single dead link.
            --}}
            @foreach ($serviceTiles as $tile)
                @continue($tile['service'] || $tile['key'] === 'work-visa')

                <span class="egho-tile">
                    <span class="egho-tile-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">{!! $tile['icon'] !!}</svg>
                    </span>

                    <span>
                        <strong>{{ $tile['title'] }}</strong>
                        <small>{{ $tile['copy'] }}</small>

                        <span class="egho-tile-status is-planned">
                            Not available yet
                        </span>
                    </span>
                </span>
            @endforeach

        </div>
    </div>
</section>

{{--
    PLACEHOLDER SECTION
    -------------------
    Cards render from the local `$popularDestinations` array in
    `home.blade.php`. The section is ready to be driven by a `destinations`
    table (with admin CRUD) — see the wiring note there. No price or
    availability claim is made here.

    The panel's "View All Destinations" button has no destination index to
    open, so the section action stays the real one this website has: the
    flight search surface.

    NOTE: never place an at-directive name (for example the php directive tag)
    inside a Blade comment. Blade compiles statements before it strips
    comments, so a stray directive token inside a comment opens a real PHP
    block and silently swallows the markup that follows it.
--}}
<section class="egho-section">
    <div class="egho-shell">

        <div class="egho-section-head">
            <div>
                <span class="egho-eyebrow">Explore</span>
                <h2>Popular Destinations</h2>
                <p>
                    Inspiration for your next journey — route search
                    covers any supported airport pair.
                </p>
            </div>

            <div class="egho-rail-head">
                @feature('flights')
                    @can('flights.search')
                        <a href="{{ route('flights.index') }}" class="egho-section-link">
                            Search flights &rarr;
                        </a>
                    @endcan
                @endfeature

                {{--
                    Rail controls.

                    Rendered hidden and revealed by the homepage script once
                    it is running, so no browser is shown a button that would
                    do nothing.
                --}}
                <div class="egho-rail-head" data-egho-rail-nav hidden>
                    <button
                        type="button"
                        class="egho-rail-button"
                        data-egho-rail-prev
                        aria-label="Show earlier destinations"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M14 6l-6 6 6 6"/>
                        </svg>
                    </button>

                    <button
                        type="button"
                        class="egho-rail-button"
                        data-egho-rail-next
                        aria-label="Show more destinations"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 6l6 6-6 6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="egho-rail" data-egho-rail>
            <div class="egho-destinations is-rail" data-egho-rail-track>
                @foreach ($popularDestinations as $destination)
                    <div
                        class="egho-destination"
                        data-egho-rail-item
                        style="--egho-destination-image: url('{{ $destination['image'] }}')"
                    >
                        <span class="egho-destination-copy">
                            <span class="egho-destination-tag">
                                {{ $destination['tag'] }}
                            </span>
                            <strong>{{ $destination['name'] }}</strong>
                            <small>{{ $destination['country'] }}</small>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>
