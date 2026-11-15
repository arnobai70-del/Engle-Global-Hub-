@extends('layouts.admin')
@section('title', 'Contact Directory')
@section('content')
<x-admin.page-header title="Contact Directory" description="Up to ten phone numbers and one 24/7 IP calling number (Affiliator contact format). Public, active numbers appear on the Support page." icon="C" eyebrow="Website CMS" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card"><p>Phone numbers: {{ $phoneCount }} / {{ \App\Models\ContactDirectoryEntry::MAX_PHONES }} · 24/7 IP line: {{ $ipCount }} / 1. Enter only numbers the business actually operates.</p></div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Type</th><th>Details</th></tr></thead><tbody>
@forelse($entries as $entry)<tr><td>{{ $entry->kind === 'ip_24x7' ? '24/7 IP calling' : 'Phone' }}<br><span class="stu-muted">{{ $entry->is_active ? 'Active' : 'Inactive' }}, {{ $entry->is_public ? 'public' : 'internal' }}</span></td>
<td>@can('content.manage')<form method="POST" action="{{ route('admin.contact-directory.update', $entry) }}" class="stu-form-grid">@csrf @method('PATCH')
<label>Label<input name="label" value="{{ $entry->label }}" maxlength="80" required></label>
<label>Number<input name="phone" value="{{ $entry->phone }}" maxlength="32" required></label>
<label>Contact name<input name="contact_name" value="{{ $entry->contact_name }}" maxlength="120"></label>
<label>Department<input name="department" value="{{ $entry->department }}" maxlength="120"></label>
<label>Availability<input name="availability" value="{{ $entry->availability }}" maxlength="120" placeholder="e.g. Sun–Thu 10am–6pm"></label>
<label>Order<input type="number" name="sort_order" value="{{ $entry->sort_order }}" min="0"></label>
<label><input type="checkbox" name="is_active" value="1" @checked($entry->is_active)> Active</label>
<label><input type="checkbox" name="is_public" value="1" @checked($entry->is_public)> Show publicly</label>
<div class="admin-form-actions"><button class="egh-button secondary">Save</button></div></form>
<form method="POST" action="{{ route('admin.contact-directory.destroy', $entry) }}" onsubmit="return confirm('Remove this number?')">@csrf @method('DELETE')<button class="egh-button danger">Remove</button></form>@else {{ $entry->label }}: {{ $entry->phone }} @endcan</td></tr>@empty<tr><td colspan="2">No numbers yet.</td></tr>@endforelse
</tbody></table></div>
@can('content.manage')
<div class="egh-card"><h2>Add number</h2><form method="POST" action="{{ route('admin.contact-directory.store') }}">@csrf
<div class="stu-form-grid">
<label>Type<select name="kind"><option value="phone">Phone number</option><option value="ip_24x7">24/7 IP calling number</option></select></label>
<label>Label<input name="label" maxlength="80" required placeholder="e.g. Student visa desk"></label>
<label>Number<input name="phone" maxlength="32" required></label>
<label>Contact name<input name="contact_name" maxlength="120"></label>
<label>Department<input name="department" maxlength="120"></label>
<label>Availability<input name="availability" maxlength="120"></label>
<label>Order<input type="number" name="sort_order" value="0" min="0"></label>
<label><input type="checkbox" name="is_active" value="1" checked> Active</label>
<label><input type="checkbox" name="is_public" value="1"> Show publicly</label>
</div><div class="admin-form-actions"><button class="egh-button">Add</button></div></form></div>
@endcan
@endsection
