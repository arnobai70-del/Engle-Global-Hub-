@props(['message' => 'Sample content shown for demonstration. Live availability will appear when the provider API is connected.'])
<div class="egh-demo-notice" role="status">
    <span class="egh-demo-badge">{{ __('Demo Preview') }}</span>
    <p>{{ __($message) }}</p>
</div>
