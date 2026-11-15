@extends('layouts.admin')
@section('title', 'SEO')
@section('content')
<x-admin.page-header title="SEO" description="Page titles, descriptions and URL redirects. Leave a field empty to keep the page's built-in text." icon="S" eyebrow="Marketing & SEO" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card"><p>The XML sitemap is generated automatically at <a class="stu-link" href="{{ route('sitemap') }}" target="_blank" rel="noopener">/sitemap.xml</a>. Submit it once in Google Search Console. Write titles for people first; do not repeat keywords.</p></div>
<div class="egh-card admin-table-card"><h2>Page metadata</h2>
<table class="stu-table"><thead><tr><th>Page</th><th>Title (max 70) and description (max 170)</th></tr></thead><tbody>
@foreach($pages as $page)<tr><td><a class="stu-link" href="{{ $page['url'] }}" target="_blank" rel="noopener">{{ $page['url'] }}</a></td>
<td><form method="POST" action="{{ route('admin.seo.pages.update') }}" style="display:grid;gap:6px">@csrf @method('PATCH')<input type="hidden" name="route" value="{{ $page['route'] }}">
<input name="title" maxlength="70" value="{{ $page['title'] }}" placeholder="Default title" aria-label="SEO title for {{ $page['url'] }}">
<textarea name="description" maxlength="170" rows="2" placeholder="Default description" aria-label="SEO description for {{ $page['url'] }}">{{ $page['description'] }}</textarea>
<button class="egh-button secondary">Save</button></form></td></tr>@endforeach
</tbody></table></div>
<div class="egh-card admin-table-card"><h2>Redirects</h2>
<table class="stu-table"><thead><tr><th>From</th><th>To</th><th>Type</th><th>Hits</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($redirects as $redirect)<tr><td>{{ $redirect->from_path }}</td><td>{{ $redirect->to_url }}</td><td>{{ $redirect->status_code === 301 ? 'Permanent (301)' : 'Temporary (302)' }}</td><td>{{ $redirect->hits }}</td><td>{{ $redirect->is_active ? 'Active' : 'Paused' }}</td>
<td><form method="POST" action="{{ route('admin.seo.redirects.update', $redirect) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $redirect->is_active ? 0 : 1 }}"><button class="egh-button secondary">{{ $redirect->is_active ? 'Pause' : 'Activate' }}</button></form>
<form method="POST" action="{{ route('admin.seo.redirects.destroy', $redirect) }}" style="display:inline" onsubmit="return confirm('Remove this redirect?')">@csrf @method('DELETE')<button class="egh-button danger">Remove</button></form></td></tr>@empty<tr><td colspan="6">No redirects.</td></tr>@endforelse
</tbody></table>
<form method="POST" action="{{ route('admin.seo.redirects.store') }}" class="stu-filter">@csrf<input name="from_path" value="{{ old('from_path') }}" placeholder="/old-page" aria-label="From path" required><input name="to_url" value="{{ old('to_url') }}" placeholder="/new-page or https://…" aria-label="To URL" required><select name="status_code" aria-label="Type"><option value="301">Permanent (301)</option><option value="302">Temporary (302)</option></select><button class="egh-button">Add redirect</button></form>
</div>
@endsection
