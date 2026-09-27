<section class="egho-section"><div class="egho-shell">
<div class="egho-section-head"><div><h2>{{ data_get($settings, 'benefits_title', 'Why Choose Eagle Global Hub?') }}</h2><p>{{ data_get($settings, 'benefits_subtitle') }}</p></div></div>
<ul class="egho-why">@foreach($whyChoose as $reason)<li><span class="egho-why-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $reason['icon'] ?? '' !!}</svg></span><span><strong>{{ $reason['title'] }}</strong><small>{{ $reason['copy'] ?? $reason['subtitle'] ?? '' }}</small></span></li>@endforeach</ul>
</div></section>

@if ($testimonials !== [])
<section class="egho-section egho-section-alt"><div class="egho-shell"><div class="egho-section-head"><div><h2>{{ data_get($settings, 'testimonials_title', 'What Our Customers Say') }}</h2><p>{{ data_get($settings, 'testimonials_subtitle') }}</p></div></div>
<div class="egho-rail" data-egho-rail><div class="egho-voices is-rail" data-egho-rail-track>
@foreach ($testimonials as $voice)
<figure class="egho-voice" data-egho-rail-item><blockquote><p>&ldquo;{{ $voice['quote'] }}&rdquo;</p></blockquote><figcaption class="egho-voice-person">@if(!empty($voice['image']))<img class="egho-voice-photo" src="{{ $voice['image'] }}" alt="{{ $voice['image_alt'] ?? $voice['name'] }}" loading="lazy">@else<span class="egho-voice-avatar" aria-hidden="true">{{ strtoupper(substr($voice['name'] ?? '?',0,1)) }}</span>@endif<span><strong>{{ $voice['name'] }}</strong><small>{{ $voice['role'] }}</small></span></figcaption></figure>
@endforeach
</div></div></div></section>
@endif
