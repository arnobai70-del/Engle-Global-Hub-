@extends('layouts.admin')
@section('title', 'Courses & Intakes')
@section('content')
<x-admin.page-header title="Courses & Intakes" description="Course catalogue per institution. Enter only information published by the institution." icon="C" eyebrow="Singapore Consultancy" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="GET" class="egh-card stu-filter"><input name="search" value="{{ $filters['search'] }}" placeholder="Course name" aria-label="Search courses"><select name="institution" aria-label="Institution"><option value="">All institutions</option>@foreach($institutions as $institution)<option value="{{ $institution->id }}" @selected((int) $filters['institution'] === $institution->id)>{{ $institution->name }}</option>@endforeach</select><button class="egh-button">Filter</button></form>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Course</th><th>Institution</th><th>Level</th><th>Duration</th><th>Open intakes</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($courses as $course)<tr><td>{{ $course->name }}</td><td>{{ $course->institution->name }}</td><td>{{ $levels[$course->study_level] ?? '—' }}</td><td>{{ $course->duration_months ? $course->duration_months.' months' : '—' }}</td><td>{{ $course->intakes_count }}</td><td>{{ $course->is_active ? 'Active' : 'Inactive' }}</td><td>@can('courses.manage')<a class="stu-link" href="{{ route('admin.courses.edit', $course) }}">Edit</a>@endcan</td></tr>@empty<tr><td colspan="7">No courses yet.@if($institutions->isEmpty()) Add an institution first.@endif</td></tr>@endforelse
</tbody></table></div>@if($courses->hasPages())<div class="admin-pagination">{{ $courses->links() }}</div>@endif
@can('courses.manage')@if($institutions->isNotEmpty())
<div class="egh-card"><h2>Add course</h2>
<form method="POST" action="{{ route('admin.courses.store') }}">@csrf
@include('admin.courses._fields', ['course' => null])
<div class="admin-form-actions"><button class="egh-button">Add course</button></div></form></div>
@endif @endcan
@endsection
