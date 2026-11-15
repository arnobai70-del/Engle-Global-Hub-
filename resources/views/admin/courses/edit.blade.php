@extends('layouts.admin')
@section('title', $course->name)
@section('content')
<x-admin.page-header :title="$course->name" :description="$course->institution->name" icon="C" eyebrow="Singapore Consultancy"><a class="egh-button secondary" href="{{ route('admin.courses.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card"><form method="POST" action="{{ route('admin.courses.update', $course) }}">@csrf @method('PATCH')
@include('admin.courses._fields', ['course' => $course])
<div class="admin-form-actions"><button class="egh-button">Save course</button></div></form></div>
<div class="egh-card admin-table-card"><h2>Intakes</h2>
<table class="stu-table"><thead><tr><th>Starts</th><th>Application deadline</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($course->intakes as $intake)<tr><td>{{ $intake->label() }}</td><td>{{ $intake->application_deadline?->format('j M Y') ?? '—' }}</td><td>{{ $intake->is_open ? 'Open' : 'Closed' }}</td><td><form method="POST" action="{{ route('admin.courses.intakes.update', [$course, $intake]) }}">@csrf @method('PATCH')<input type="hidden" name="is_open" value="{{ $intake->is_open ? 0 : 1 }}"><button class="egh-button secondary">{{ $intake->is_open ? 'Close' : 'Reopen' }}</button></form></td></tr>@empty<tr><td colspan="4">No intakes yet.</td></tr>@endforelse
</tbody></table>
<form method="POST" action="{{ route('admin.courses.intakes.store', $course) }}" class="stu-filter">@csrf<label>Start month<input type="month" name="starts_on" required></label><label>Deadline<input type="date" name="application_deadline"></label><button class="egh-button">Add intake</button></form>
</div>
@endsection
