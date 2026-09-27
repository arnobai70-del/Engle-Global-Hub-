<section class="egho-section egho-section-alt"><div class="egho-shell">
<div class="egho-section-head"><div><h2>{{ data_get($settings, 'services_title', 'Our Services') }}</h2><p>{{ data_get($settings, 'services_subtitle') }}</p></div></div>
<div class="egho-tiles">
@foreach ($serviceTiles as $tile)
    @php
        $key = $tile['key'] ?? '';
        $serviceKey = $tile['service'] ?? null;
        $tileLink = $key === 'work-visa' ? route('work-visa.index') : ($serviceKey ? $serviceLink($serviceKey) : ($tile['url'] ?? null));
        $tileStatus = $serviceKey ? $serviceStatus($serviceKey) : null;
    @endphp
    @if ($tileLink)
        <a href="{{ $tileLink }}" class="egho-tile"><span class="egho-tile-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $tile['icon'] ?? '' !!}</svg></span><span><strong>{{ $tile['title'] }}</strong><small>{{ $tile['copy'] ?? $tile['subtitle'] ?? '' }}</small>@if ($tileStatus)<span @class(['egho-tile-status','is-live'=>$tileStatus['live']])>{{ $tileStatus['label'] }}</span>@endif</span></a>
    @else
        <span class="egho-tile"><span class="egho-tile-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $tile['icon'] ?? '' !!}</svg></span><span><strong>{{ $tile['title'] }}</strong><small>{{ $tile['copy'] ?? $tile['subtitle'] ?? '' }}</small>@if ($tileStatus)<span @class(['egho-tile-status','is-live'=>$tileStatus['live']])>{{ $tileStatus['label'] }}</span>@elseif (! $serviceKey && $key !== 'work-visa')<span class="egho-tile-status is-planned">Not available yet</span>@endif</span></span>
    @endif
@endforeach
</div></div></section>

<section class="egho-section"><div class="egho-shell">
<div class="egho-section-head"><div><h2>{{ data_get($settings, 'destinations_title', 'Popular Destinations') }}</h2><p>{{ data_get($settings, 'destinations_subtitle') }}</p></div><div class="egho-rail-head">@feature('flights') @can('flights.search')<a href="{{ route('flights.index') }}" class="egho-section-link">View All Destinations &rarr;</a>@endcan @endfeature<div class="egho-rail-head" data-egho-rail-nav hidden><button type="button" class="egho-rail-button" data-egho-rail-prev aria-label="Show earlier destinations">‹</button><button type="button" class="egho-rail-button" data-egho-rail-next aria-label="Show more destinations">›</button></div></div></div>
<div class="egho-rail" data-egho-rail data-egho-autoplay="continuous" data-egho-speed="36"><div class="egho-destinations is-rail" data-egho-rail-track>
@foreach ($popularDestinations as $destination)
<div class="egho-destination" data-egho-rail-item style="--egho-destination-image: url('{{ $destination['image'] ?? '' }}')" role="img" aria-label="{{ $destination['image_alt'] ?? (($destination['name'] ?? '').' '.($destination['country'] ?? '')) }}"><span class="egho-destination-copy"><span class="egho-destination-tag">{{ $destination['tag'] ?? '' }}</span><strong>{{ $destination['name'] }}</strong><small>{{ $destination['country'] }}</small></span></div>
@endforeach
</div></div></div></section>
