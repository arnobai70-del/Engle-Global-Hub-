<section class="egho-section"><div class="egho-shell"><div class="egho-app">
<div><h2>{{ data_get($settings, 'app_title', 'Download Our Mobile App') }}</h2><p>{{ data_get($settings, 'app_subtitle') }}</p><ul class="egho-app-chips">@foreach($appFeatures as $feature)<li><svg viewBox="0 0 24 24" aria-hidden="true">{!! $feature['icon'] ?? '' !!}</svg>{{ $feature['title'] }}</li>@endforeach</ul></div>
<div class="egho-stores">
@if(data_get($settings,'google_play_url'))<a class="egho-store" href="{{ data_get($settings,'google_play_url') }}" rel="noopener noreferrer"><span><small>GET IT ON</small><strong>Google Play</strong></span></a>@else<span class="egho-store"><span><small>Google Play</small><strong>No published build</strong></span></span>@endif
@if(data_get($settings,'app_store_url'))<a class="egho-store" href="{{ data_get($settings,'app_store_url') }}" rel="noopener noreferrer"><span><small>Download on the</small><strong>App Store</strong></span></a>@else<span class="egho-store"><span><small>App Store</small><strong>No published build</strong></span></span>@endif
</div></div></div></section>

<section class="egho-journey" style="--egho-journey-image: url('{{ data_get($settings, 'journey_background') }}')"><div class="egho-shell egho-journey-inner">
<div><h2>{{ data_get($settings,'newsletter_title',data_get($settings,'journey_title')) }}</h2><p>{{ data_get($settings,'newsletter_subtitle',data_get($settings,'journey_copy')) }}</p></div>
<form class="egho-newsletter" method="POST" action="{{ route('newsletter.subscribe') }}">@csrf<label class="egho-visually-hidden" for="newsletter-email">Email address</label><input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" autocomplete="email" required><button type="submit">Subscribe</button></form>
</div>@if(session('newsletter_status'))<div class="egho-shell"><p class="egho-newsletter-status" role="status">{{ session('newsletter_status') }}</p></div>@endif @error('email')<div class="egho-shell"><p class="egho-newsletter-status is-error" role="alert">{{ $message }}</p></div>@enderror</section>
