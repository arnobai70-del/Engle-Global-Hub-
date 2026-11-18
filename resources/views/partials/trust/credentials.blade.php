{{-- Company credentials (Admin › Business facts), each hidden when empty. @var array{trade_licence: ?string, registration: ?string, memberships: list<string>, maps_url: ?string, reviews_url: ?string} $credentials --}}
@if(array_filter($credentials) !== [])
<div class="v2-credentials">
    <dl>
        @if($credentials['trade_licence'])<div><dt>{{ __('Trade licence') }}</dt><dd>{{ $credentials['trade_licence'] }}</dd></div>@endif
        @if($credentials['registration'])<div><dt>{{ __('Company registration') }}</dt><dd>{{ $credentials['registration'] }}</dd></div>@endif
        @if($credentials['memberships'] !== [])<div><dt>{{ __('Memberships') }}</dt><dd><ul>@foreach($credentials['memberships'] as $membership)<li>{{ $membership }}</li>@endforeach</ul></dd></div>@endif
    </dl>
    @if($credentials['maps_url'] || $credentials['reviews_url'])
    <div class="v2-actions">
        @if($credentials['maps_url'])<a class="v2-btn v2-btn-outline" href="{{ $credentials['maps_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Find us on Google Maps') }}</a>@endif
        @if($credentials['reviews_url'])<a class="v2-btn v2-btn-outline" href="{{ $credentials['reviews_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Read our Google reviews') }}</a>@endif
    </div>
    @endif
</div>
@endif
