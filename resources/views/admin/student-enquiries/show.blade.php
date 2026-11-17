@extends('layouts.admin')
@section('title', 'Student Enquiry '.$enquiry->reference)
@section('content')
<x-admin.page-header :title="$enquiry->reference" description="Study consultation enquiry." icon="S" eyebrow="Singapore Consultancy"><a class="egh-button secondary" href="{{ route('admin.student-enquiries.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card">
<dl class="stu-show">
<dt>Name</dt><dd>{{ $enquiry->full_name }}</dd>
<dt>Email</dt><dd>{{ $enquiry->email ?: "Not given (phone / WhatsApp lead)" }}</dd>
<dt>Phone</dt><dd>{{ $enquiry->phone }}</dd>
<dt>Destination</dt><dd>{{ $enquiry->destinationCountry?->name ?? 'Not specified' }}</dd>
<dt>Study level</dt><dd>{{ $studyLevels[$enquiry->study_level] ?? 'Not specified' }}</dd>
<dt>Highest qualification</dt><dd>{{ $enquiry->highest_qualification ?: 'Not specified' }}</dd>
<dt>Preferred intake</dt><dd>{{ $enquiry->preferred_intake ?: 'Not specified' }}</dd>
<dt>Consultation requested</dt><dd>{{ $enquiry->wants_consultation ? 'Yes' : 'No' }}</dd>
<dt>Lead source</dt><dd>{{ $leadSources[$enquiry->lead_source] ?? $enquiry->lead_source }}@if($enquiry->referredByAgent) — referred by agent <a class="stu-link" href="{{ route('admin.agents.show', $enquiry->referredByAgent) }}">{{ $enquiry->referredByAgent->name }}</a>@endif</dd>
<dt>Consent recorded</dt><dd>{{ $enquiry->consented_at->format('Y-m-d H:i') }}</dd>
<dt>Message</dt><dd class="stu-notes">{{ $enquiry->message ?: 'None' }}</dd>
@if($enquiry->student)<dt>Student profile</dt><dd><a class="stu-link" href="{{ route('admin.students.show', $enquiry->student) }}">{{ $enquiry->student->reference_code }}</a></dd>@endif
@foreach($enquiry->applications as $application)<dt>Application</dt><dd><a class="stu-link" href="{{ route('admin.student-applications.show', $application) }}">{{ $application->reference }}</a> — {{ $application->status->label() }}</dd>@endforeach
</dl>
</div>

@can('student-enquiries.manage')
<div class="egh-card">
<h2>Lead follow-up</h2>
<form method="POST" action="{{ route('admin.student-enquiries.update', $enquiry) }}">@csrf @method('PATCH')
<div class="stu-form-grid">
@if($enquiry->status === \App\Models\StudentEnquiry::STATUS_CONVERTED)
<p>Status: Converted</p>
@else
<label>Status<select name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $enquiry->status) === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select></label>
@endif
<label>Assigned to<select name="assigned_to_user_id"><option value="">Unassigned</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected((string) old('assigned_to_user_id', $enquiry->assigned_to_user_id) === (string) $member->id)>{{ $member->name }}</option>@endforeach</select></label>
<label>Email (needed before opening an application)<input type="email" name="email" maxlength="254" value="{{ old('email', $enquiry->email) }}"></label>
<label>Follow-up date<input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at', $enquiry->follow_up_at?->format('Y-m-d\TH:i')) }}"></label>
<label class="stu-full">Internal notes (staff only)<textarea name="internal_notes" maxlength="5000">{{ old('internal_notes', $enquiry->internal_notes) }}</textarea></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Save follow-up</button></div>
</form>
</div>
@endcan

@if($enquiry->status !== \App\Models\StudentEnquiry::STATUS_CONVERTED)
@can('student-applications.manage')
<div class="egh-card">
<h2>Convert to student application</h2>
<p>Creates or reuses the student profile with this email address, then opens an application at "New Enquiry".</p>
<form method="POST" action="{{ route('admin.student-enquiries.convert', $enquiry) }}">@csrf
@include('admin.student-applications._fields', ['application' => null, 'defaultCountryId' => $enquiry->destination_country_id])
<div class="admin-form-actions"><button class="egh-button">Convert and open application</button></div>
</form>
</div>
@endcan
@endif
@endsection
