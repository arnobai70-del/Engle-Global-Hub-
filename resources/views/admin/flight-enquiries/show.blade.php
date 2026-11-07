@extends('layouts.admin')
@section('title', 'Flight Enquiry '.$enquiry->reference)
@section('content')
<x-admin.page-header :title="$enquiry->reference" description="Manual enquiry and quotation. A status change does not issue a ticket." icon="F" eyebrow="Travel CRM"/>
<div class="egh-card" style="max-width:800px">
    @if(session('status'))<p role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <dl><dt>Customer</dt><dd>{{ $enquiry->contact_name }} — {{ $enquiry->contact_email }} — {{ $enquiry->contact_phone }}</dd>
    <dt>Route</dt><dd>{{ $enquiry->origin }} → {{ $enquiry->destination }}</dd>
    <dt>Travel dates</dt><dd>{{ $enquiry->departure_date->toDateString() }} @if($enquiry->return_date) → {{ $enquiry->return_date->toDateString() }} @endif</dd>
    <dt>Passengers</dt><dd>{{ $enquiry->adults }} adult(s), {{ $enquiry->children }} child(ren), {{ $enquiry->infants }} infant(s)</dd>
    <dt>Cabin</dt><dd>{{ $enquiry->cabin_class }}</dd></dl>
    <form method="POST" action="{{ route('admin.flight-enquiries.update',$enquiry) }}" style="display:grid;gap:16px;max-width:520px">
        @csrf @method('PATCH')
        <label>Status <select name="status">@foreach(['new_enquiry','reviewing','quotation_sent','accepted','payment_confirmed','ticket_issued','cancelled','expired'] as $status)<option value="{{ $status }}" @selected(old('status',$enquiry->status)===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select></label>
        <label>Final quotation (BDT; airline taxes included) <input type="number" step="0.01" min="0.01" name="quoted_total_bdt" value="{{ old('quoted_total_bdt',$enquiry->quoted_total_bdt) }}"></label>
        <label>Internal notes <textarea name="admin_notes" rows="4">{{ old('admin_notes',$enquiry->admin_notes) }}</textarea></label>
        <p>Enter only verified fares. Record ticket issuance only after external issuer confirmation. No ticketing API is called here.</p>
        <button class="egh-button">Save CRM record</button>
    </form>
    <p><a href="{{ route('admin.flight-enquiries.index') }}">All enquiries</a></p>
</div>
@endsection
