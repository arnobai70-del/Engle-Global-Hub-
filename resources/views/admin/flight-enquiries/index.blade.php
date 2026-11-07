@extends('layouts.admin')
@section('title', 'Flight Enquiries')
@section('content')
<x-admin.page-header title="Flight Enquiries" description="Manual fare enquiries — no automated reservations or ticket issuance." icon="F" eyebrow="Travel CRM"/>
<div class="egh-card"><table><thead><tr><th>Reference</th><th>Customer</th><th>Route</th><th>Departure</th><th>Status</th><th>Quote (BDT)</th><th>Created</th><th></th></tr></thead><tbody>
@forelse($enquiries as $enquiry)
<tr><td>{{ $enquiry->reference }}</td><td>{{ $enquiry->contact_name }}<br>{{ $enquiry->contact_email }}</td><td>{{ $enquiry->origin }} → {{ $enquiry->destination }}</td><td>{{ $enquiry->departure_date->toDateString() }}</td><td>{{ str_replace('_',' ',$enquiry->status) }}</td><td>{{ $enquiry->quoted_total_bdt ?? 'Pending' }}</td><td>{{ $enquiry->created_at->format('Y-m-d') }}</td><td><a href="{{ route('admin.flight-enquiries.show',$enquiry) }}">Review</a></td></tr>
@empty<tr><td colspan="8">No flight enquiries yet.</td></tr>@endforelse
</tbody></table>{{ $enquiries->links() }}</div>
@endsection
