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
@can('appointments.manage')
<section class="egh-card" aria-labelledby="booking-hours-title" id="booking-hours">
    <h2 id="booking-hours-title">Public booking hours</h2>
    <p class="stu-muted">Visitors can book a free consultation at <a class="stu-link" href="{{ url('/book-appointment') }}" target="_blank" rel="noopener">/book-appointment</a> in these hours (office time, Dhaka). Times already taken by a requested or confirmed appointment are not offered. Public bookings appear in this list as confirmed and unassigned, and the visitor gets a confirmation email. {{ $bookingLive ? 'Public booking is ON.' : 'Public booking is OFF until every field below is filled in.' }}</p>
    <form method="POST" action="{{ route('admin.appointments.booking-hours') }}" class="stu-form-grid">@csrf @method('PATCH')
        <fieldset class="stu-full"><legend>Open on</legend>@foreach(\App\Services\Consultancy\BookingSlots::WEEKDAYS as $day => $label)<label style="display:inline-flex;gap:6px;margin-right:14px"><input type="checkbox" name="days[]" value="{{ $day }}" @checked(in_array($day, old('days', $booking['days']), false))> {{ $label }}</label>@endforeach</fieldset>
        <label>Opening time<input type="time" name="start" value="{{ old('start', $booking['start']) }}"></label>
        <label>Closing time (last slot ends by then)<input type="time" name="end" value="{{ old('end', $booking['end']) }}"></label>
        <label>Slot length (minutes)<input type="number" name="slot_minutes" min="15" max="240" value="{{ old('slot_minutes', $booking['slot_minutes']) }}"></label>
        <label>Bookable days ahead<input type="number" name="days_ahead" min="1" max="90" value="{{ old('days_ahead', $booking['days_ahead']) }}"></label>
        <label>Minimum notice (hours, optional)<input type="number" name="notice_hours" min="0" max="168" value="{{ old('notice_hours', $booking['notice_hours'] ?: '') }}"></label>
        <fieldset class="stu-full"><legend>Meeting types offered</legend>@foreach(\App\Models\Appointment::MODES as $mode => $label)<label style="display:inline-flex;gap:6px;margin-right:14px"><input type="checkbox" name="modes[]" value="{{ $mode }}" @checked(in_array($mode, old('modes', $booking['modes']), true))> {{ $label }}</label>@endforeach</fieldset>
        <div class="admin-form-actions"><button class="egh-button" type="submit">Save booking hours</button></div>
    </form>
</section>
@endcan
@endsection
