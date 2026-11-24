@extends('layouts.admin')
@section('title', 'Team')
@section('content')
<x-admin.page-header title="Team" description="Management and staff profiles. Drafts are not shown anywhere on the website." icon="T" eyebrow="Website CMS">
@can('content.manage')<a class="egh-button" href="{{ route('admin.team-members.create') }}">Add Team Member</a>@endcan
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card"><p>Profiles imported from the requirements document are saved as <strong>drafts</strong>. Verify each name and role before publishing.</p></div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Order</th><th>Name</th><th>Role</th><th>Photo</th><th>Visibility</th><th>Action</th></tr></thead><tbody>
@forelse($members as $member)<tr><td>{{ $member->sort_order }}</td><td>{{ $member->name }}</td><td>{{ $member->role_title }}</td><td>
@if($member->photo_path)
<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}" alt="Photo of {{ $member->name }}" width="48" height="48" loading="lazy" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
@else
<span aria-label="No photo">—</span>
@endif
</td><td>{{ $member->is_published ? 'Published' : 'Draft (unverified)' }}</td><td>@can('content.manage')<a class="stu-link" href="{{ route('admin.team-members.edit', $member) }}">Edit</a>@endcan</td></tr>@empty<tr><td colspan="6">No team members yet.</td></tr>@endforelse
</tbody></table></div>
@endsection
