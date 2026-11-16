@extends('layouts.site')
@section('title', 'My Enquiries')
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<x-site.portal active="enquiries" kind="tourist">
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ __('My enquiries') }}</h1>
            @if($enquiries->isEmpty())
                <p>{{ __('You have not sent any travel or visa enquiries while signed in.') }}</p>
            @else
                <ul>
                    @foreach($enquiries as $enquiry)
                        <li><strong>{{ $enquiry->reference }}</strong> — {{ __($enquiry->service->label()) }}@if($enquiry->categoryLabel()) ({{ __($enquiry->categoryLabel()) }})@endif @if($enquiry->destinationCountry), {{ $enquiry->destinationCountry->name }}@endif @if($enquiry->route_or_destination), {{ $enquiry->route_or_destination }}@endif — {{ $enquiry->statusLabel() }} <small>({{ $enquiry->created_at->format('j M Y') }})</small></li>
                    @endforeach
                </ul>
                {{ $enquiries->links() }}
            @endif
            <p><a href="{{ route('service-enquiries.create', 'tourist-visa') }}">{{ __('Send a new enquiry') }}</a></p>
        </section>
    </div>
</main>
</x-site.portal>
@endsection
