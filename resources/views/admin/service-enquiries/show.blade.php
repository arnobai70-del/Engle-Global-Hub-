@extends('layouts.admin')
@section('title', 'Enquiry '.$enquiry->reference)
@section('content')
<x-admin.page-header :title="$enquiry->reference" :description="$enquiry->service->label().' enquiry'" icon="E" eyebrow="Travel & Visa"><a class="egh-button secondary" href="{{ route('admin.service-enquiries.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card"><dl class="stu-show">
<dt>Customer</dt><dd>{{ $enquiry->full_name }} — {{ $enquiry->email }} — {{ $enquiry->phone }}</dd>
@if($enquiry->categoryLabel())<dt>Type</dt><dd>{{ $enquiry->categoryLabel() }}</dd>@endif
@if($enquiry->referredByAgent)<dt>Referred by</dt><dd><a class="stu-link" href="{{ route('admin.agents.show', $enquiry->referredByAgent) }}">{{ $enquiry->referredByAgent->company_name ?: $enquiry->referredByAgent->name }}</a></dd>@endif
<dt>Account</dt><dd>{{ $enquiry->user ? 'Signed-in customer' : 'Guest' }}</dd>
<dt>Destination</dt><dd>{{ $enquiry->destinationCountry?->name ?? $enquiry->route_or_destination ?? 'Not specified' }}</dd>
@if($enquiry->destinations->isNotEmpty())<dt>Places to visit</dt><dd>{{ $enquiry->destinations->pluck('name')->implode(', ') }}</dd>@endif
<dt>Dates</dt><dd>{{ $enquiry->travel_date?->format('Y-m-d') ?? 'Flexible' }}@if($enquiry->return_date) → {{ $enquiry->return_date->format('Y-m-d') }}@endif</dd>
<dt>Travellers</dt><dd>{{ $enquiry->travellers }}</dd>
<dt>Message</dt><dd class="stu-notes">{{ $enquiry->message ?: 'None' }}</dd>
<dt>Consent recorded</dt><dd>{{ $enquiry->consented_at->format('Y-m-d H:i') }}</dd>
</dl></div>
@can('service-enquiries.manage')
<div class="egh-card"><h2>Follow-up</h2>
<p>"Confirmed by staff" is a manual record. Record it only after the booking or visa service is confirmed outside this system.</p>
<form method="POST" action="{{ route('admin.service-enquiries.update', $enquiry) }}">@csrf @method('PATCH')
<div class="stu-form-grid">
<label>Status<select name="status">@foreach(\App\Models\ServiceEnquiry::STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('status', $enquiry->status) === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Assigned to<select name="assigned_to_user_id"><option value="">Unassigned</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected((string) old('assigned_to_user_id', $enquiry->assigned_to_user_id) === (string) $member->id)>{{ $member->name }}</option>@endforeach</select></label>
<label class="stu-full">Internal notes (staff only)<textarea name="internal_notes" maxlength="5000">{{ old('internal_notes', $enquiry->internal_notes) }}</textarea></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Save</button></div>
</form></div>
@endcan
@endsection
