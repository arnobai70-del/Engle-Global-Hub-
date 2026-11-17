@php
    $facts = app(\App\Services\SiteFacts::class);
    $barWhatsapp = $facts->whatsappUrl(__('Hello Eagle Global Hub, I would like a free consultation.'));
@endphp
<nav class="v2-mobile-bar" aria-label="{{ __('Quick contact') }}">
    @if($facts->telUrl())<a href="{{ $facts->telUrl() }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>{{ __('Call') }}</a>@endif
    @if($barWhatsapp)<a class="is-whatsapp" href="{{ $barWhatsapp }}" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l1.3-3.8A8 8 0 1 1 8 19.2z"/></svg>WhatsApp</a>@endif
    <a class="is-apply" href="{{ route('consultancy.malaysia') }}#apply"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 13h6M9 17h4"/></svg>{{ __('Apply') }}</a>
</nav>
