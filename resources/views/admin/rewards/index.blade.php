@extends('layouts.admin')
@section('title', 'Rewards')
@section('content')
<x-admin.page-header title="Rewards" description="Loyalty programmes stay hidden until their terms and earning rules are written and published. Points carry no cash value unless the terms say so." icon="R" eyebrow="Marketing" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@foreach($programs as $program)
<div class="egh-card"><h2>{{ $program->name }} — {{ $program->is_published ? 'Published' : 'Unpublished' }}</h2><p class="stu-muted">{{ $program->transactions_count }} ledger entries</p>
<form method="POST" action="{{ route('admin.rewards.update', $program) }}">@csrf @method('PATCH')
<div class="stu-form-grid">
<label>Name<input name="name" value="{{ $program->name }}" maxlength="150" required></label>
<label>Slug<input name="slug" value="{{ $program->slug }}" maxlength="160"></label>
<label>Starts<input type="date" name="starts_on" value="{{ $program->starts_on?->format('Y-m-d') }}"></label>
<label>Ends<input type="date" name="ends_on" value="{{ $program->ends_on?->format('Y-m-d') }}"></label>
<label>Points expire after (days)<input type="number" name="points_expire_after_days" value="{{ $program->points_expire_after_days }}" min="1"></label>
<label class="stu-full">Description<textarea name="description" maxlength="1000">{{ $program->description }}</textarea></label>
<label class="stu-full">Earning rules<textarea name="earning_rules" maxlength="5000">{{ $program->earning_rules }}</textarea></label>
<label class="stu-full">Terms and conditions<textarea name="terms" maxlength="10000" rows="5">{{ $program->terms }}</textarea></label>
<label><input type="checkbox" name="is_published" value="1" @checked($program->is_published)> Published</label>
</div><div class="admin-form-actions"><button class="egh-button">Save</button></div></form></div>
@endforeach
<div class="egh-card"><h2>New programme</h2><form method="POST" action="{{ route('admin.rewards.store') }}">@csrf
<div class="stu-form-grid"><label>Name<input name="name" maxlength="150" required></label><label class="stu-full">Description<textarea name="description" maxlength="1000"></textarea></label></div>
<div class="admin-form-actions"><button class="egh-button">Create (unpublished)</button></div></form></div>
@if($programs->where('is_published', true)->isNotEmpty())
<div class="egh-card"><h2>Award or deduct points</h2><form method="POST" action="{{ route('admin.rewards.adjust') }}">@csrf
<div class="stu-form-grid">
<label>Programme<select name="reward_program_id">@foreach($programs->where('is_published', true) as $program)<option value="{{ $program->id }}">{{ $program->name }}</option>@endforeach</select></label>
<label>Customer email<input type="email" name="customer_email" required></label>
<label>Points (negative to deduct)<input type="number" name="points" required></label>
<label>Reason<input name="reason" maxlength="200" required></label>
</div><div class="admin-form-actions"><button class="egh-button">Record</button></div></form></div>
@endif
<div class="egh-card admin-table-card"><h2>Recent ledger entries</h2><table class="stu-table"><thead><tr><th>When</th><th>Customer</th><th>Programme</th><th>Points</th><th>Reason</th><th>By</th></tr></thead><tbody>
@forelse($recent as $entry)<tr><td>{{ $entry->created_at->format('Y-m-d') }}</td><td>{{ $entry->user?->email }}</td><td>{{ $entry->program?->name }}</td><td>{{ $entry->points }}</td><td>{{ $entry->reason }}</td><td>{{ $entry->createdBy?->name }}</td></tr>@empty<tr><td colspan="6">No entries.</td></tr>@endforelse
</tbody></table></div>
@endsection
