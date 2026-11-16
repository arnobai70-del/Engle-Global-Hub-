@props(['state'])
@php($label = \App\Services\Integrations\ProviderStatus::LABELS[$state] ?? \App\Services\Integrations\ProviderStatus::LABELS['waiting'])
<span {{ $attributes->class(['sg-status', 'sg-status-upper', 'is-'.$state]) }} data-provider-state="{{ $state }}">{{ __($label) }}</span>
