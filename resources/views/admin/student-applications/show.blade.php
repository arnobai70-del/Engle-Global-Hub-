@extends('layouts.admin')
@section('title', 'Application '.$application->reference)
@section('content')
<x-admin.page-header :title="$application->reference" :description="$application->status->stage().' · '.$application->status->label()" icon="S" eyebrow="Singapore Consultancy"><a class="egh-button secondary" href="{{ route('admin.student-applications.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="egh-card">
<dl class="stu-show">
<dt>Student</dt><dd><a class="stu-link" href="{{ route('admin.students.show', $application->student) }}">{{ $application->student->first_name }} {{ $application->student->last_name }}</a> — {{ $application->student->email }}</dd>
<dt>Portal login</dt><dd>{{ $application->student->user_id ? 'Linked' : 'Not linked (the student can sign in with a verified account using this email)' }}</dd>
<dt>Destination</dt><dd>{{ $application->destinationCountry->name }}</dd>
<dt>Institution</dt><dd>{{ $application->institution?->name ?? 'Not selected yet' }}</dd>
<dt>Course</dt><dd>{{ $application->course_name ?: 'Not selected yet' }}</dd>
<dt>Intake</dt><dd>{{ $application->intake ?: 'Not selected yet' }}</dd>
<dt>Consultant</dt><dd>{{ $application->assignedConsultant?->name ?? 'Unassigned' }}</dd>
@if($application->enquiry)<dt>Source enquiry</dt><dd><a class="stu-link" href="{{ route('admin.student-enquiries.show', $application->enquiry) }}">{{ $application->enquiry->reference }}</a></dd>@endif
</dl>
</div>

@can('student-applications.manage')
@if($nextStatuses !== [])
<div class="egh-card">
<h2>Change status</h2>
<p>Record a decision only after it has been received from the institution or ICA. Changing a status here does not submit anything to any external system.</p>
<form method="POST" action="{{ route('admin.student-applications.transition', $application) }}">@csrf
<div class="stu-form-grid">
<label>New status<select name="status" required>@foreach($nextStatuses as $status)<option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->label() }} ({{ $status->stage() }})</option>@endforeach</select></label>
<label class="stu-full">Note for the student (shown on their timeline)<textarea name="student_note" maxlength="1000">{{ old('student_note') }}</textarea></label>
<label class="stu-full">Internal note (staff only)<textarea name="internal_note" maxlength="5000">{{ old('internal_note') }}</textarea></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Update status</button></div>
</form>
</div>
@else
<div class="egh-card"><p>This application is closed ({{ $application->status->label() }}). No further status changes are possible.</p></div>
@endif

<div class="egh-card">
<h2>Application details</h2>
<form method="POST" action="{{ route('admin.student-applications.update', $application) }}">@csrf @method('PATCH')
@include('admin.student-applications._fields', ['application' => $application])
<div class="stu-form-grid"><label class="stu-full">Internal notes (staff only)<textarea name="internal_notes" maxlength="5000">{{ old('internal_notes', $application->internal_notes) }}</textarea></label></div>
<div class="admin-form-actions"><button class="egh-button">Save details</button></div>
</form>
</div>
@endcan

<div class="egh-card admin-table-card">
<h2>Documents</h2>
<p>Files are stored on a private disk and only download through this page for authorised staff.</p>
<table class="stu-table"><thead><tr><th>Type</th><th>File</th><th>Uploaded</th><th>Status</th><th>Review</th></tr></thead><tbody>
@forelse($application->documents as $document)<tr>
<td>{{ $document->document_type->label() }}</td>
<td><a class="stu-link" href="{{ route('admin.student-applications.documents.download', [$application, $document]) }}">{{ $document->original_name }}</a><br><span class="stu-muted">{{ strtoupper(\App\Services\Consultancy\StudentDocumentStore::ALLOWED_TYPES[$document->mime_type] ?? '') }} · {{ number_format($document->size_bytes / 1024, 0) }} KB</span></td>
<td>{{ $document->created_at->format('Y-m-d H:i') }}<br><span class="stu-muted">{{ $document->uploadedBy?->name ?? '—' }}</span></td>
<td>{{ $document->statusLabel() }}@if($document->reviewedBy)<br><span class="stu-muted">{{ $document->reviewedBy->name }}, {{ $document->reviewed_at->format('Y-m-d') }}</span>@endif @if($document->review_note)<br><span class="stu-notes">{{ $document->review_note }}</span>@endif</td>
<td>
@can('student-documents.verify')
<form method="POST" action="{{ route('admin.student-applications.documents.review', [$application, $document]) }}">@csrf @method('PATCH')
<select name="status" aria-label="Review decision"><option value="verified">Verify</option><option value="rejected">Reject</option></select>
<input name="review_note" maxlength="1000" placeholder="Note for student (required to reject)" aria-label="Review note">
<button class="egh-button">Save</button></form>
@endcan
@can('student-applications.manage')
<form method="POST" action="{{ route('admin.student-applications.documents.destroy', [$application, $document]) }}" onsubmit="return confirm('Permanently delete this document?')">@csrf @method('DELETE')<button class="egh-button danger">Delete</button></form>
@endcan
</td></tr>@empty<tr><td colspan="5">No documents uploaded yet.</td></tr>@endforelse
</tbody></table>
@can('student-applications.manage')
@if($application->acceptsUploads())
<form method="POST" action="{{ route('admin.student-applications.documents.store', $application) }}" enctype="multipart/form-data">@csrf
<div class="stu-form-grid">
<label>Document type<select name="document_type" required>@foreach($documentTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></label>
<label>File (PDF, JPG, PNG; max 10 MB)<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Upload on behalf of student</button></div>
</form>
@endif
@endcan
</div>

<div class="egh-card admin-table-card">
<h2>Appointments</h2>
<table class="stu-table"><thead><tr><th>When</th><th>Mode</th><th>Staff</th><th>Status</th><th>Update</th></tr></thead><tbody>
@forelse($application->appointments as $appointment)<tr><td>{{ $appointment->scheduled_at->format('Y-m-d H:i') }}@if($appointment->student_note)<br><span class="stu-notes">{{ $appointment->student_note }}</span>@endif</td><td>{{ \App\Models\Appointment::MODES[$appointment->mode] ?? $appointment->mode }}</td><td>{{ $appointment->staff?->name ?? 'Unassigned' }}</td><td>{{ $appointment->statusLabel() }}</td><td>@can('appointments.manage')@include('admin.appointments._update', ['appointment' => $appointment])@endcan</td></tr>@empty<tr><td colspan="5">No appointments.</td></tr>@endforelse
</tbody></table>
@can('appointments.manage')
<form method="POST" action="{{ route('admin.appointments.store') }}" class="stu-filter">@csrf
<input type="hidden" name="student_application_id" value="{{ $application->id }}"><input type="hidden" name="duration_minutes" value="30"><input type="hidden" name="status" value="confirmed">
<input type="datetime-local" name="scheduled_at" aria-label="Date and time" required>
<select name="mode" aria-label="Mode">@foreach(\App\Models\Appointment::MODES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
<select name="staff_user_id" aria-label="Staff">@foreach($staff as $member)<option value="{{ $member->id }}" @selected((int) $application->assigned_consultant_id === $member->id)>{{ $member->name }}</option>@endforeach</select>
<input name="location_or_link" maxlength="500" placeholder="Office address or meeting link" aria-label="Location or link">
<button class="egh-button">Book confirmed appointment</button></form>
@endcan
</div>

<div class="egh-card admin-table-card">
<h2>Payments</h2>
<table class="stu-table"><thead><tr><th>Reference</th><th>Amount</th><th>Paid on</th><th>Status</th></tr></thead><tbody>
@forelse($application->payments as $payment)<tr><td>@can('payments.view')<a class="stu-link" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->reference }}</a>@else{{ $payment->reference }}@endcan</td><td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td><td>{{ $payment->paid_on->format('Y-m-d') }}</td><td>{{ $payment->statusLabel() }}</td></tr>@empty<tr><td colspan="4">No payments recorded.</td></tr>@endforelse
</tbody></table>
@can('payments.record')<a class="egh-button" href="{{ route('admin.payments.create', ['payable_type' => 'student_application', 'payable_id' => $application->id]) }}">Record offline payment</a>@endcan
</div>

<div class="egh-card">
<h2>Messages with the student</h2>
<p>The student can read every message here. Use internal notes for staff-only information.</p>
@forelse($application->messages as $message)
<article class="stu-notes"><strong>{{ $message->author_type === \App\Models\StudentApplicationMessage::AUTHOR_STUDENT ? 'Student' : ($message->author?->name ?? 'Staff') }}</strong> <span class="stu-muted">{{ $message->created_at->format('Y-m-d H:i') }}</span><p>{{ $message->body }}</p></article>
@empty
<p>No messages yet.</p>
@endforelse
@can('student-applications.manage')
<form method="POST" action="{{ route('admin.student-applications.messages.store', $application) }}">@csrf
<div class="stu-form-grid"><label class="stu-full">Reply to student<textarea name="body" maxlength="3000" required></textarea></label></div>
<div class="admin-form-actions"><button class="egh-button">Send message</button></div>
</form>
@endcan
</div>

<div class="egh-card admin-table-card">
<h2>Status history</h2>
<table class="stu-table"><thead><tr><th>Date</th><th>From</th><th>To</th><th>By</th><th>Student note</th><th>Internal note</th></tr></thead><tbody>
@foreach($application->statusChanges as $change)<tr><td>{{ $change->created_at->format('Y-m-d H:i') }}</td><td>{{ $change->from_status?->label() ?? '—' }}</td><td>{{ $change->to_status->label() }}</td><td>{{ $change->changedBy?->name ?? 'System' }}</td><td class="stu-notes">{{ $change->student_note ?: '—' }}</td><td class="stu-notes">{{ $change->internal_note ?: '—' }}</td></tr>@endforeach
</tbody></table>
</div>
@endsection
