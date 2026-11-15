@extends('layouts.site')
@section('title', 'Agent Portal')
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<main class="egho-page">
    <div class="egho-shell">
        <section class="egho-contact-card">
            <h1>{{ $agent->partner_type === \App\Models\Agent::TYPE_B2B_PARTNER ? __('Egabe B2B partner portal') : __('Agent portal') }}</h1>
            <p>{{ $agent->company_name ?: $agent->name }}</p>
            @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
            <p><a class="egho-btn egho-btn-primary" href="{{ route('agent.referrals.create') }}">{{ __('Refer a student') }}</a> <a class="egho-btn" href="{{ route('agent.service-referrals.create') }}">{{ __('Refer a travel or business enquiry') }}</a></p>
            <h2>{{ __('Your student referrals') }}</h2>
            @if($referrals->isEmpty())
                <p>You have not referred any students yet.</p>
            @else
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr><th scope="col" style="text-align:left">Reference</th><th scope="col" style="text-align:left">Student</th><th scope="col" style="text-align:left">Destination</th><th scope="col" style="text-align:left">Progress</th><th scope="col" style="text-align:left">Referred</th></tr></thead>
                    <tbody>
                        @foreach($referrals as $referral)
                            <tr>
                                <td>{{ $referral->reference }}</td>
                                <td>{{ $referral->full_name }}</td>
                                <td>{{ $referral->destinationCountry?->name ?? '—' }}</td>
                                <td>@forelse($referral->applications as $application){{ __($application->status->stage()) }}: {{ __($application->status->label()) }}@if(! $loop->last)<br>@endif @empty{{ ucwords(str_replace('_', ' ', $referral->status)) }}@endforelse</td>
                                <td>{{ $referral->created_at->format('j M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $referrals->links() }}
            @endif
            <h2>{{ __('Your travel and business referrals') }}</h2>
            @forelse($serviceReferrals as $referral)
                <p><strong>{{ $referral->reference }}</strong> — {{ __($referral->service->label()) }}@if($referral->categoryLabel()) ({{ __($referral->categoryLabel()) }})@endif — {{ $referral->full_name }} — {{ __($referral->statusLabel()) }} <small>{{ $referral->created_at->format('j M Y') }}</small></p>
            @empty
                <p>{{ __('No travel or business referrals yet.') }}</p>
            @endforelse
        </section>
    </div>
</main>
@endsection
