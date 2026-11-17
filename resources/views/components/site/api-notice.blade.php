@props(['capability', 'state' => null, 'enquiryUrl' => null, 'enquiryLabel' => null])
@php
    $providerStatus = app(\App\Services\Integrations\ProviderStatus::class);
    $state ??= $providerStatus->of($capability);
    $feature = __(\App\Services\Integrations\ProviderStatus::CAPABILITIES[$capability]['label'] ?? 'This feature');
@endphp
@if($state !== \App\Services\Integrations\ProviderStatus::LIVE)
<div {{ $attributes->class(['v2-soon']) }} role="status" data-api-notice="{{ $capability }}">
    <x-site.provider-badge :state="$state" />
    <div>
        <strong>{{ $feature }}</strong>
        <p>
            @if($state === \App\Services\Integrations\ProviderStatus::DISABLED)
                {{ __('This feature is switched off at the moment.') }}
            @else
                {{ __('Online booking for this is coming soon. Our team can help you today: send an enquiry and we reply personally.') }}
            @endif
        </p>
    </div>
    @if($enquiryUrl)<a class="v2-btn v2-btn-red" href="{{ $enquiryUrl }}">{{ $enquiryLabel ?? __('Send an enquiry') }}</a>@endif
</div>
@endif
