<section class="egho-section egho-section-alt"><div class="egho-shell"><div class="egho-panels">
@foreach ($servicePanels as $panel)
    @php
        $isWorkVisa = (bool) ($panel['work_visa'] ?? false);
        $panelServiceKey = $panel['service'] ?? null;
        $visibilityKey = $isWorkVisa
            ? 'visa'
            : (is_string($panelServiceKey) && $panelServiceKey !== '' ? $panelServiceKey : null);
        $isVisible = $visibilityKey === null
            || app(\App\Services\Feature\FeatureManager::class)->isVisibleTo($visibilityKey, auth()->user());
        $panelLink = $isWorkVisa
            ? route('work-visa.apply')
            : (! empty($panel['service']) ? $serviceLink($panel['service']) : ($panel['url'] ?? null));
    @endphp
    @if ($isVisible)
        <div class="egho-panel {{ $panel['class'] ?? '' }}">
            <div><h3>{{ $panel['title'] }}</h3><p>{{ $panel['copy'] ?? $panel['subtitle'] ?? '' }}</p>
                @if (! empty($panel['items']) && is_array($panel['items']))<ul class="egho-panel-list">@foreach($panel['items'] as $item)<li><span aria-hidden="true">&#10003;</span>{{ $item }}</li>@endforeach</ul>@endif
                @if ($panelLink && !empty($panel['cta']))<a href="{{ $panelLink }}" class="egho-panel-cta">{{ $panel['cta'] }} <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M13 7l5 5-5 5"/></svg></a>@elseif(!empty($panel['cta']))<span class="egho-panel-status">Available once the related provider is configured</span>@else<span class="egho-panel-status">Not available yet</span>@endif
            </div>
            <div class="egho-panel-media" style="--egho-panel-image: url('{{ $panel['image'] ?? '' }}')" role="img" aria-label="{{ $panel['image_alt'] ?? $panel['title'] }}"></div>
        </div>
    @endif
@endforeach
</div></div></section>
