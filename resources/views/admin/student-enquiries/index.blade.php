@extends('layouts.admin')
@section('title', 'Student Enquiries')
@section('content')
<x-admin.page-header title="Student Enquiries" description="Study consultation leads from the Study in Singapore page." icon="S" eyebrow="Singapore Consultancy" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<form method="GET" class="egh-card stu-filter"><input name="search" value="{{ $filters['search'] }}" placeholder="Name, email, phone or reference" aria-label="Search enquiries"><select name="status" aria-label="Status"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select><button class="egh-button">Filter</button></form>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Reference</th><th>Name</th><th>Contact</th><th>Destination</th><th>Consultation</th><th>Source</th><th>Status</th><th>Assigned</th><th>Received</th><th>Action</th></tr></thead><tbody>
@forelse($enquiries as $enquiry)<tr><td>{{ $enquiry->reference }}</td><td>{{ $enquiry->full_name }}</td><td>{{ $enquiry->email }}<br><span class="stu-muted">{{ $enquiry->phone }}</span></td><td>{{ $enquiry->destinationCountry?->name ?? '—' }}</td><td>{{ $enquiry->wants_consultation ? 'Requested' : '—' }}</td><td>{{ \App\Models\StudentEnquiry::LEAD_SOURCES[$enquiry->lead_source] ?? $enquiry->lead_source }}</td><td>{{ ucwords(str_replace('_', ' ', $enquiry->status)) }}</td><td>{{ $enquiry->assignedTo?->name ?? 'Unassigned' }}</td><td>{{ $enquiry->created_at->format('Y-m-d') }}</td><td><a class="stu-link" href="{{ route('admin.student-enquiries.show', $enquiry) }}">Review</a></td></tr>@empty<tr><td colspan="10">No student enquiries found.</td></tr>@endforelse
</tbody></table></div>@if($enquiries->hasPages())<div class="admin-pagination">{{ $enquiries->links() }}</div>@endif
@endsection
