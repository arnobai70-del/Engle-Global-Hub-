@extends('layouts.admin')
@section('title', 'Eligibility rules')
@section('content')
<x-admin.page-header title="Eligibility rules" description="Entry rules behind the public eligibility checker (Study in Malaysia › Eligibility checker). A student sees a course level when their answers meet every limit you fill in. Enter only rules you can stand behind; the checker page stays hidden until one active rule exists." icon="E" eyebrow="Malaysia Consultancy">
    @if(\App\Models\EligibilityRule::query()->where('is_active', true)->exists())<a class="egh-button secondary" href="{{ route('consultancy.malaysia.eligibility') }}" target="_blank" rel="noopener">Open the checker</a>@endif
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Course level</th><th>Qualifications</th><th>Min. result</th><th>Min. English</th><th>Max. gap</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($rules as $rule)
<tr data-rule="{{ $rule->id }}"><td><strong>{{ $levels[$rule->study_level] ?? $rule->study_level }}</strong>@if($rule->note_en)<br><span class="stu-muted">{{ $rule->note_en }}</span>@endif</td><td>{{ collect($rule->qualifications)->map(fn ($key) => $qualifications[$key] ?? $key)->implode(', ') }}</td><td>{{ $rule->min_gpa !== null ? number_format($rule->min_gpa, 2) : '—' }}</td><td>{{ $rule->min_english !== null ? number_format($rule->min_english, 1) : '—' }}</td><td>{{ $rule->max_gap_years !== null ? $rule->max_gap_years.' years' : '—' }}</td><td>{{ $rule->is_active ? 'Active' : 'Inactive' }}</td>
<td>@can('courses.manage')<a class="stu-link" href="{{ route('admin.eligibility-rules.edit', $rule) }}">Edit</a>
<form method="POST" action="{{ route('admin.eligibility-rules.destroy', $rule) }}" style="display:inline" onsubmit="return confirm('Delete this rule?')">@csrf @method('DELETE')<button class="egh-button danger" type="submit">Delete</button></form>@endcan</td></tr>
@empty<tr><td colspan="7">No rules yet. The eligibility checker is hidden until you add one.</td></tr>@endforelse
</tbody></table></div>
@can('courses.manage')
<div class="egh-card"><h2>Add rule</h2>
<form method="POST" action="{{ route('admin.eligibility-rules.store') }}">@csrf
@include('admin.eligibility-rules._fields', ['rule' => null])
<div class="admin-form-actions"><button class="egh-button">Add rule</button></div></form></div>
@endcan
@endsection
