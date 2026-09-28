<section class="egho-section egho-section-tight"><div class="egho-shell"><ul class="egho-assurances">
@foreach ($assurances as $assurance)
<li><span class="egho-assurance-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $assurance['icon'] ?? '' !!}</svg></span><span class="egho-assurance-copy"><strong>{{ $assurance['title'] }}</strong><small>{{ $assurance['subtitle'] ?? $assurance['copy'] ?? '' }}</small></span></li>
@endforeach
</ul></div></section>

<section class="egho-section"><div class="egho-shell"><div class="egho-promos">
@foreach ($promoBanners as $promo)
    @php
        $isWorkVisa = (bool) ($promo['work_visa'] ?? false);
        $feature = $promo['feature'] ?? null;
        $visibilityKey = $isWorkVisa ? 'visa' : $feature;
        $promoLink = $isWorkVisa ? route('work-visa.apply') : ($feature ? $serviceLink($feature) : ($promo['url'] ?? null));
    @endphp
    @if (! $visibilityKey || app(\App\Services\Feature\FeatureManager::class)->isVisibleTo($visibilityKey, auth()->user()))
        <div @class(['egho-promo', 'is-workvisa' => $isWorkVisa]) style="--egho-promo-image: url('{{ $promo['image'] ?? '' }}')">
            <span class="egho-promo-badge">{{ $promo['badge'] ?? '' }}</span><h2>{{ $promo['title'] }}</h2><p>{{ $promo['copy'] ?? $promo['subtitle'] ?? '' }}</p>
            @if (! empty($promo['items']) && is_array($promo['items']))<ul class="egho-promo-list">@foreach ($promo['items'] as $item)<li><span aria-hidden="true">&#10003;</span>{{ $item }}</li>@endforeach</ul>@endif
            @if ($promoLink && ! empty($promo['cta']))<a href="{{ $promoLink }}" class="egho-promo-cta">{{ $promo['cta'] }} <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M13 7l5 5-5 5"/></svg></a>@elseif (! empty($promo['cta']))<span class="egho-promo-cta">{{ $promo['cta'] }}</span>@endif
        </div>
    @endif
@endforeach
</div></div></section>
