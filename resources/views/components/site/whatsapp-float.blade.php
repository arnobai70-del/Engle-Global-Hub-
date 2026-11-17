@php($whatsappUrl = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello Eagle Global Hub, I would like a free consultation.')))
@if($whatsappUrl)
<a class="sg-wa-float" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Chat with us on WhatsApp') }}" data-whatsapp-float>
    <svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 3.2A12.7 12.7 0 0 0 5.1 22.4L3.3 28.8l6.6-1.7A12.7 12.7 0 1 0 16 3.2zm0 23.2c-2 0-3.9-.5-5.6-1.5l-.4-.2-3.9 1 1-3.8-.3-.4A10.5 10.5 0 1 1 16 26.4zm5.8-7.9c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2l-1 1.2c-.2.2-.4.2-.7.1a8.6 8.6 0 0 1-4.3-3.7c-.3-.5.3-.5.9-1.6.1-.2 0-.4 0-.5l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4s-1.2 1.1-1.2 2.8 1.2 3.2 1.4 3.4c.2.2 2.4 3.6 5.7 5.1 2.1.9 3 1 4 .8.7-.1 1.9-.8 2.2-1.5.3-.8.3-1.4.2-1.5 0-.2-.3-.3-.5-.4z"/></svg>
    <span class="sg-visually-hidden">WhatsApp</span>
</a>
@endif
