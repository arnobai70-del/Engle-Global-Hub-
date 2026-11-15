@extends('layouts.admin')
@section('title', 'Translations')
@section('content')
<x-admin.page-header title="Translations" description="Edit website interface text (menus, buttons, labels) in English or Bengali. Leave a field empty to use the default text." icon="L" eyebrow="System Settings" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="GET" class="egh-card stu-filter"><select name="locale" aria-label="Language">@foreach($locales as $code => $label)<option value="{{ $code }}" @selected($locale === $code)>{{ $label }}</option>@endforeach</select><input name="search" value="{{ $search }}" placeholder="Search English or translated text" aria-label="Search translations"><button class="egh-button">Filter</button></form>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>English</th><th>Default translation</th><th>Override</th></tr></thead><tbody>
@forelse($rows as $row)<tr>
<td>{{ $row['source'] }}</td>
<td lang="{{ $locale }}">{{ $row['file'] }}</td>
<td>@can('content.manage')<form method="POST" action="{{ route('admin.translations.update') }}" style="display:flex;gap:6px;flex-wrap:wrap">@csrf @method('PATCH')<input type="hidden" name="locale" value="{{ $locale }}"><input type="hidden" name="source_hash" value="{{ $row['hash'] }}"><textarea name="value" rows="2" lang="{{ $locale }}" aria-label="Override for {{ \Illuminate\Support\Str::limit($row['source'], 60) }}" style="min-width:220px;flex:1">{{ $row['override'] }}</textarea><button class="egh-button secondary">Save</button></form>@else{{ $row['override'] ?? '—' }}@endcan</td>
</tr>@empty<tr><td colspan="3">No translations found.</td></tr>@endforelse
</tbody></table></div>
@endsection
