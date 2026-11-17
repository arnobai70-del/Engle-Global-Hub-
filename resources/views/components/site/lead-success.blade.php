@php($whatsappUrl = app(\App\Services\SiteFacts::class)->whatsappUrl(__('Hello, my enquiry reference is :reference.', ['reference' => (string) session('lead_reference', session('enquiry_reference'))])))
@if(session('lead_reference', session('enquiry_reference')))
<div class="v2-success" role="status" data-lead-success>
    <strong>{{ __('Thank you, we received your request.') }}</strong>
    <p>{{ __('A consultant will contact you soon. Your reference is :reference.', ['reference' => session('lead_reference', session('enquiry_reference'))]) }}</p>
    @if($whatsappUrl)<p><a class="v2-link" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('Need a faster reply? Message us on WhatsApp') }}</a></p>@endif
</div>
@endif
