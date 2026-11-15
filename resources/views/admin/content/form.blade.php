@extends('layouts.admin')
@section('title', $entry->exists ? $entry->title : 'New '.$entry->type->label())
@section('content')
@php($type = $entry->type)
<x-admin.page-header :title="$entry->exists ? $entry->title : 'New '.$type->label()" :description="$type->label().' · '.ucfirst($entry->status ?? 'draft')" icon="W" eyebrow="Website CMS"><a class="egh-button secondary" href="{{ route('admin.content.index', ['type' => $type->value]) }}">Back</a>@if($entry->exists) <a class="egh-button secondary" href="{{ route('admin.content.preview', $entry) }}" target="_blank" rel="noopener">Preview</a>@endif</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card">
<p>Write plain text. Leave a blank line between paragraphs. HTML is removed. @if($type->requiresOfficialSource())<strong>State only facts you can confirm from the official sources listed below (for example ICA, MOE or the institution). Do not state fees, approval rates or processing times unless the source says so.</strong>@endif @if($type->requiresConsent())<strong>Publish only with the student's written consent.</strong>@endif @if($type->requiresOwnerApproval())<strong>Registration details, certificates and completed projects must be approved by the owner before publishing. Editing the text removes an earlier approval.</strong>@endif</p>
<form method="POST" action="{{ $entry->exists ? route('admin.content.update', $entry) : route('admin.content.store') }}">@csrf @if($entry->exists) @method('PATCH') @else <input type="hidden" name="type" value="{{ $type->value }}"> @endif
<div class="stu-form-grid">
<label>Title<input name="title" value="{{ old('title', $entry->title) }}" maxlength="200" required></label>
@if($type === \App\Enums\ContentType::PageSection)
<label>Page section<select name="slug" required>@foreach($pageSections as $slug => $label)<option value="{{ $slug }}" @selected(old('slug', $entry->slug) === $slug)>{{ $label }}</option>@endforeach</select></label>
@else
<label>URL slug<input name="slug" value="{{ old('slug', $entry->slug) }}" maxlength="160" placeholder="generated from the title"></label>
@endif
<label>Category<input name="category" value="{{ old('category', $entry->category) }}" maxlength="100" placeholder="{{ $type === \App\Enums\ContentType::BusinessService ? 'export-import or civil-construction' : '' }}"></label>
<label>Display order<input type="number" name="sort_order" min="0" value="{{ old('sort_order', $entry->sort_order ?? 0) }}" required></label>
<label class="stu-full">Summary<textarea name="summary" maxlength="500" rows="2">{{ old('summary', $entry->summary) }}</textarea></label>
<label class="stu-full">Body<textarea name="body" maxlength="30000" rows="10">{{ old('body', $entry->body) }}</textarea></label>
<label>Bengali title (needs native review)<input name="title_bn" lang="bn" value="{{ old('title_bn', $entry->title_bn) }}" maxlength="200"></label>
<label class="stu-full">Bengali summary<textarea name="summary_bn" lang="bn" maxlength="500" rows="2">{{ old('summary_bn', $entry->summary_bn) }}</textarea></label>
<label class="stu-full">Bengali body<textarea name="body_bn" lang="bn" maxlength="30000" rows="6">{{ old('body_bn', $entry->body_bn) }}</textarea></label>
@if($type->hasLocation() || in_array($type, [\App\Enums\ContentType::CompanyProfile, \App\Enums\ContentType::BusinessService], true))
<label>Location / address<input name="location" value="{{ old('location', $entry->location) }}" maxlength="200"></label>
<label>Contact phone<input name="contact_phone" value="{{ old('contact_phone', $entry->contact_phone) }}" maxlength="32"></label>
<label>Contact email<input type="email" name="contact_email" value="{{ old('contact_email', $entry->contact_email) }}" maxlength="254"></label>
<label>Website (https)<input type="url" name="website_url" value="{{ old('website_url', $entry->website_url) }}" maxlength="2048"></label>
<label>Latitude (for a future map)<input name="latitude" value="{{ old('latitude', $entry->latitude) }}" inputmode="decimal"></label>
<label>Longitude<input name="longitude" value="{{ old('longitude', $entry->longitude) }}" inputmode="decimal"></label>
@endif
<label>Cover image (from Media Library)<select name="cover_media_id"><option value="">None</option>@foreach($media as $asset)<option value="{{ $asset->id }}" @selected((string) old('cover_media_id', $entry->cover_media_id) === (string) $asset->id)>{{ $asset->alt_text }}</option>@endforeach</select></label>
<label>Gallery images<select name="gallery_media_ids[]" multiple size="5">@foreach($media as $asset)<option value="{{ $asset->id }}" @selected(collect(old('gallery_media_ids', $entry->exists ? $entry->gallery->pluck('id')->all() : []))->contains($asset->id))>{{ $asset->alt_text }}</option>@endforeach</select></label>
<label class="stu-full">Sources (one https link per line{{ $type->requiresOfficialSource() ? '; required to publish' : '' }})<textarea name="source_urls_text" rows="3">{{ old('source_urls_text', implode("\n", $entry->source_urls ?? [])) }}</textarea></label>
@if($type->requiresConsent())
<label>Consent reference (e.g. signed form ID)<input name="consent_reference" value="{{ old('consent_reference', $entry->consent_reference) }}" maxlength="200"></label>
<label>Consent date<input type="date" name="consent_recorded_at" value="{{ old('consent_recorded_at', $entry->consent_recorded_at?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}"></label>
@endif
<label>Related destinations<select name="related_destination_ids[]" multiple size="4">@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(collect(old('related_destination_ids', $entry->related_destination_ids ?? []))->contains($destination->id))>{{ $destination->name }}</option>@endforeach</select></label>
<label>Related content (internal links)<select name="related_entry_ids[]" multiple size="4">@foreach($otherEntries as $other)@if($other->id !== $entry->id)<option value="{{ $other->id }}" @selected(collect(old('related_entry_ids', $entry->related_entry_ids ?? []))->contains($other->id))>{{ $other->title }} ({{ $other->type->label() }})</option>@endif @endforeach</select></label>
<label>Review status<select name="review_status"><option value="">Not tracked</option>@foreach(\App\Models\ContentEntry::REVIEW_STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('review_status', $entry->review_status) === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Facts last checked against sources<input type="date" name="last_verified_at" value="{{ old('last_verified_at', $entry->last_verified_at?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}"></label>
<label class="stu-full">Internal review notes (never shown publicly)<textarea name="review_notes" maxlength="5000" rows="3">{{ old('review_notes', $entry->review_notes) }}</textarea></label>
<label>SEO title (max 70)<input name="seo_title" value="{{ old('seo_title', $entry->seo_title) }}" maxlength="70"></label>
<label>SEO description (max 170)<input name="seo_description" value="{{ old('seo_description', $entry->seo_description) }}" maxlength="170"></label>
<label>Canonical URL (only if different)<input type="url" name="canonical_url" value="{{ old('canonical_url', $entry->canonical_url) }}" maxlength="2048"></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Save draft changes</button></div>
</form>
</div>
@if($entry->exists)
<div class="egh-card"><h2>Publishing</h2>
<p>Status: <strong>{{ ucfirst($entry->status) }}</strong>@if($entry->publicUrl()) — <a class="stu-link" href="{{ $entry->publicUrl() }}" target="_blank" rel="noopener">view public page</a>@endif</p>
@if($type->requiresOwnerApproval())<p>Owner approval: {{ $entry->owner_approved_at ? 'approved by '.($entry->approvedBy?->name ?? 'owner').' on '.$entry->owner_approved_at->format('j M Y') : 'not approved' }}</p>
@can('content.approve')@if(! $entry->owner_approved_at)<form method="POST" action="{{ route('admin.content.approve', $entry) }}">@csrf<button class="egh-button secondary">Approve claims as owner</button></form>@endif @endcan @endif
@if($entry->publishBlockers() !== [])<ul>@foreach($entry->publishBlockers() as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>@endif
<div class="stu-filter">@foreach(['published' => 'Publish', 'draft' => 'Move to draft', 'archived' => 'Archive'] as $value => $label)@if($entry->status !== $value)<form method="POST" action="{{ route('admin.content.status', $entry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $value }}"><button class="egh-button {{ $value === 'published' ? '' : 'secondary' }}">{{ $label }}</button></form>@endif @endforeach</div>
</div>
<div class="egh-card admin-table-card"><h2>Revision history</h2>
<table class="stu-table"><thead><tr><th>Saved</th><th>By</th><th>Status</th><th></th></tr></thead><tbody>
@foreach($entry->revisions->take(20) as $revision)<tr><td>{{ $revision->created_at->format('Y-m-d H:i') }}</td><td>{{ $revision->user?->name ?? '—' }}</td><td>{{ $revision->status }}</td><td>@if(! $loop->first)<form method="POST" action="{{ route('admin.content.restore', $entry) }}">@csrf<input type="hidden" name="revision" value="{{ $revision->id }}"><button class="egh-button secondary">Restore</button></form>@endif</td></tr>@endforeach
</tbody></table></div>
@endif
@endsection
