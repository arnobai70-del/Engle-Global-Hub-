@props(['state'])
@php
    $label = \App\Services\Integrations\ProviderStatus::PUBLIC_LABELS[$state] ?? \App\Services\Integrations\ProviderStatus::PUBLIC_LABELS['enquiry'];
    $tone = match ($state) { 'live' => 'v2-pill-live', 'enquiry' => 'v2-pill-enquiry', default => 'v2-pill-soon' };
@endphp
<span {{ $attributes->class(['v2-pill', $tone]) }} data-provider-state="{{ $state }}">{{ __($label) }}</span>
