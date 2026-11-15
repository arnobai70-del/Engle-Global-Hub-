@extends('layouts.admin')
@section('title', 'Forum Moderation')
@section('content')
<x-admin.page-header title="Forum Moderation" description="Categories, posts awaiting approval and reported posts." icon="F" eyebrow="Community" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card admin-table-card"><h2>Reported posts</h2><table class="stu-table"><thead><tr><th>Post</th><th>Reason</th><th>Action</th></tr></thead><tbody>
@forelse($reports as $report)<tr><td><a class="stu-link" href="{{ route('forums.thread', [$report->post->thread->category->slug, $report->post->thread->slug]) }}#post-{{ $report->post->id }}">{{ $report->post->thread->title }}</a><br><span class="stu-notes">{{ \Illuminate\Support\Str::limit($report->post->body, 200) }}</span></td><td>{{ $report->reason }}<br><span class="stu-muted">{{ $report->user?->name }}</span></td><td>@include('admin.forums._moderate', ['post' => $report->post])</td></tr>@empty<tr><td colspan="3">No open reports.</td></tr>@endforelse
</tbody></table></div>
<div class="egh-card admin-table-card"><h2>Awaiting approval</h2><table class="stu-table"><thead><tr><th>Post</th><th>Author</th><th>Action</th></tr></thead><tbody>
@forelse($pending as $post)<tr><td>{{ $post->thread->title }}<br><span class="stu-notes">{{ \Illuminate\Support\Str::limit($post->body, 300) }}</span></td><td>{{ $post->user?->name }}<br><span class="stu-muted">{{ $post->user?->email }}</span></td><td>@include('admin.forums._moderate', ['post' => $post])</td></tr>@empty<tr><td colspan="3">Nothing waiting.</td></tr>@endforelse
</tbody></table></div>
<div class="egh-card admin-table-card"><h2>Categories</h2><table class="stu-table"><thead><tr><th>Name</th><th>Topics</th><th>Settings</th></tr></thead><tbody>
@foreach($categories as $category)<tr><td>{{ $category->name }}<br><span class="stu-muted">/forums/{{ $category->slug }}</span></td><td>{{ $category->threads_count }}</td><td><form method="POST" action="{{ route('admin.forums.categories.update', $category) }}" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center">@csrf @method('PATCH')<select name="visibility" aria-label="Visibility">@foreach(\App\Models\ForumCategory::VISIBILITIES as $visibility)<option value="{{ $visibility }}" @selected($category->visibility === $visibility)>{{ ucfirst($visibility) }}</option>@endforeach</select><label><input type="checkbox" name="requires_approval" value="1" @checked($category->requires_approval)> Approve posts first</label><label><input type="checkbox" name="is_active" value="1" @checked($category->is_active)> Open</label><button class="egh-button secondary">Save</button></form></td></tr>@endforeach
</tbody></table>
<form method="POST" action="{{ route('admin.forums.categories.store') }}" class="stu-filter">@csrf<input name="name" placeholder="New category name" maxlength="100" required aria-label="Category name"><input name="description" placeholder="Description" maxlength="500" aria-label="Description"><select name="visibility" aria-label="Visibility">@foreach(\App\Models\ForumCategory::VISIBILITIES as $visibility)<option value="{{ $visibility }}">{{ ucfirst($visibility) }}</option>@endforeach</select><label><input type="checkbox" name="requires_approval" value="1" checked> Approve posts first</label><button class="egh-button">Add category</button></form>
</div>
@endsection
