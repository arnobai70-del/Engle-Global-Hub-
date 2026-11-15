@extends('layouts.admin')
@section('title', 'Upcoming Success')
@section('content')
<x-admin.page-header title="Upcoming Success" description="Internal pipeline of applications after an offer letter. Shows the next possible step, not a predicted outcome. Never shown publicly." icon="U" eyebrow="Singapore Consultancy" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Application</th><th>Student</th><th>Current status</th><th>Possible next steps</th><th>Consultant</th><th>Follow-up</th></tr></thead><tbody>
@forelse($applications as $application)<tr>
<td><a class="stu-link" href="{{ route('admin.student-applications.show', $application) }}">{{ $application->reference }}</a><br><span class="stu-muted">{{ $application->destinationCountry->name }}</span></td>
<td>{{ $application->student->first_name }} {{ $application->student->last_name }}</td>
<td>{{ $application->status->label() }}<br><span class="stu-muted">{{ $application->status->stage() }}</span></td>
<td>{{ collect($application->status->allowedTransitions())->reject(fn ($status) => $status->isClosed() && $status !== \App\Enums\StudentApplicationStatus::Completed)->map->label()->implode(', ') ?: '—' }}</td>
<td>{{ $application->assignedConsultant?->name ?? 'Unassigned' }}</td>
<td>@can('student-applications.manage')<form method="POST" action="{{ route('admin.success-pipeline.follow-up', $application) }}" style="display:flex;gap:6px">@csrf @method('PATCH')<input type="date" name="follow_up_at" value="{{ $application->follow_up_at?->format('Y-m-d') }}" aria-label="Follow-up date for {{ $application->reference }}"><button class="egh-button secondary">Save</button></form>@else{{ $application->follow_up_at?->format('Y-m-d') ?? '—' }}@endcan
@if($application->follow_up_at && $application->follow_up_at->isPast())<span class="stu-muted">Overdue</span>@endif</td>
</tr>@empty<tr><td colspan="6">No applications in the late stages.</td></tr>@endforelse
</tbody></table></div>@if($applications->hasPages())<div class="admin-pagination">{{ $applications->links() }}</div>@endif
@endsection
