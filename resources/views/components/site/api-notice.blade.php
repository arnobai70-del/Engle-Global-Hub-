@props(['capability', 'state' => null, 'enquiryUrl' => null, 'enquiryLabel' => null])
@php
    $providerStatus = app(\App\Services\Integrations\ProviderStatus::class);
    $state ??= $providerStatus->of($capability);
    $feature = __(\App\Services\Integrations\ProviderStatus::CAPABILITIES[$capability]['label'] ?? 'This feature');
@endphp
@if($state !== \App\Services\Integrations\ProviderStatus::LIVE)
<div {{ $attributes->class(['sg-api-notice', 'is-'.$state]) }} role="status" data-api-notice="{{ $capability }}">
    <x-site.provider-badge :state="$state" />
    <div>
        <strong>{{ $feature }}</strong>
        <p>
            @if($state === \App\Services\Integrations\ProviderStatus::DISABLED)
                {{ __('This feature is switched off at the moment.') }}
            @elseif($state === \App\Services\Integrations\ProviderStatus::DEMO)
                {{ __('Local demo data only. It is never shown as live and nothing can be booked.') }}
            @else
                {{ __('This will be available after the provider API is connected and tested. Nothing is booked or charged here yet.') }}
            @endif
            @if(! $enquiryUrl){{ __('There is no online alternative for this yet.') }}@endif
        </p>
    </div>
    @if($enquiryUrl)<a class="sg-btn sg-btn-red sg-btn-sm" href="{{ $enquiryUrl }}">{{ $enquiryLabel ?? __('Send an enquiry instead') }}</a>@endif
</div>
@endif
