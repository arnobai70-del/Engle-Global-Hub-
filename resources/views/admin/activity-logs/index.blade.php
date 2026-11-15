@extends('layouts.admin')
@section('title', 'Activity Logs')
@section('content')
<x-admin.page-header title="Activity Logs" description="Who changed business records, and when. Sensitive values are never recorded." icon="L" eyebrow="Users & Security" />
<form method="GET" class="egh-card stu-filter"><input name="action" value="{{ $action }}" placeholder="Action prefix, e.g. student_document" aria-label="Filter by action"><button class="egh-button">Filter</button></form>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>When</th><th>User</th><th>Action</th><th>Record</th><th>Details</th><th>IP</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td><td>{{ $log->user?->name ?? 'System' }}</td><td>{{ $log->action }}</td><td>{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</td><td>@foreach($log->properties ?? [] as $key => $value){{ $key }}: {{ $value }}@if(! $loop->last), @endif @endforeach</td><td>{{ $log->ip_address ?? '—' }}</td></tr>@empty<tr><td colspan="6">No activity recorded yet.</td></tr>@endforelse
</tbody></table></div>@if($logs->hasPages())<div class="admin-pagination">{{ $logs->links() }}</div>@endif
@endsection
