@extends('layouts.admin')
@section('title', 'Appointments')
@section('content')
<x-admin.page-header title="Appointments" description="Consultation appointments. Confirmed slots cannot overlap for the same staff member." icon="A" eyebrow="Singapore Consultancy" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card stu-filter"><a class="egh-button {{ $status === '' ? '' : 'secondary' }}" href="{{ route('admin.appointments.index') }}">Upcoming</a>@foreach(\App\Models\Appointment::STATUSES as $value => $label) <a class="egh-button {{ $status === $value ? '' : 'secondary' }}" href="{{ route('admin.appointments.index', ['status' => $value]) }}">{{ $label }}</a>@endforeach</div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>When</th><th>With</th><th>Mode</th><th>Staff</th><th>Status</th><th>Update</th></tr></thead><tbody>
@forelse($appointments as $appointment)<tr>
<td>{{ $appointment->scheduled_at->format('Y-m-d H:i') }}<br><span class="stu-muted">{{ $appointment->duration_minutes }} min</span></td>
<td>@if($appointment->application)<a class="stu-link" href="{{ route('admin.student-applications.show', $appointment->application) }}">{{ $appointment->application->student->first_name }} {{ $appointment->application->student->last_name }}</a>@elseif($appointment->enquiry)<a class="stu-link" href="{{ route('admin.student-enquiries.show', $appointment->enquiry) }}">{{ $appointment->enquiry->full_name }}</a>@endif @if($appointment->student_note)<br><span class="stu-notes">{{ $appointment->student_note }}</span>@endif</td>
<td>{{ \App\Models\Appointment::MODES[$appointment->mode] ?? $appointment->mode }}</td>
<td>{{ $appointment->staff?->name ?? 'Unassigned' }}</td>
<td>{{ $appointment->statusLabel() }}</td>
<td>@can('appointments.manage')@include('admin.appointments._update', ['appointment' => $appointment])@endcan</td>
</tr>@empty<tr><td colspan="6">No appointments.</td></tr>@endforelse
</tbody></table></div>@if($appointments->hasPages())<div class="admin-pagination">{{ $appointments->links() }}</div>@endif
@endsection
