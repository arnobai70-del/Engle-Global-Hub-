@extends('layouts.admin')
@section('title', 'Content Approval')
@section('content')
<x-admin.page-header title="Content Approval" description="Owner review queue for every unpublished entry. Approving does not publish anything: an approved entry stays a draft until it is published from the editor." icon="A" eyebrow="Owner review"><a class="egh-button secondary" href="{{ route('admin.content.index') }}">All content</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<nav class="egh-card stu-filter approval-filter" aria-label="Filter by review status">
<a class="egh-button {{ $reviewStatus === '' ? '' : 'secondary' }}" href="{{ route('admin.content.approvals') }}">All unpublished ({{ $counts->sum() }})</a>
@foreach(\App\Models\ContentEntry::REVIEW_STATUSES as $key => $label)
<a class="egh-button {{ $reviewStatus === $key ? '' : 'secondary' }}" href="{{ route('admin.content.approvals', ['review' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
@endforeach
</nav>

@forelse($entries as $entry)
@php($blockers = $entry->publishBlockers())
@php($awaitingSources = $entry->review_status === 'needs_source_verification')
<article class="egh-card approval-item" id="entry-{{ $entry->id }}" aria-labelledby="entry-{{ $entry->id }}-title">
<header class="approval-head">
<div><h2 id="entry-{{ $entry->id }}-title" class="approval-title">{{ $entry->title }}</h2>
<p class="stu-muted">{{ $entry->type->label() }}@if($entry->category) · {{ $entry->category }}@endif · {{ ucfirst($entry->status) }} · Last updated {{ $entry->updated_at->format('d M Y') }}</p></div>
<span class="approval-badge is-{{ $entry->review_status ?? 'none' }}">{{ \App\Models\ContentEntry::REVIEW_STATUSES[$entry->review_status] ?? 'No review status' }}</span>
</header>

<div class="approval-previews">
<section><h3>English</h3>@if($entry->summary)<p><strong>{{ $entry->summary }}</strong></p>@endif<p>{{ \Illuminate\Support\Str::limit((string) $entry->body, 420) ?: 'No English body yet.' }}</p></section>
<section lang="bn"><h3>বাংলা</h3>@if($entry->title_bn)<p><strong>{{ $entry->title_bn }}</strong></p>@endif<p>{{ \Illuminate\Support\Str::limit((string) ($entry->summary_bn ?: $entry->body_bn), 420) ?: 'No Bengali text yet.' }}</p><p class="stu-muted" lang="en">Bengali needs a native speaker's review before publishing.</p></section>
</div>

<dl class="approval-meta">
<dt>Official sources</dt><dd>@forelse($entry->source_urls ?? [] as $url)<a class="stu-link" href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ parse_url($url, PHP_URL_HOST) }}{{ \Illuminate\Support\Str::limit((string) parse_url($url, PHP_URL_PATH), 40) }}</a>@if(! $loop->last)<br>@endif @empty <span class="stu-muted">None listed{{ $entry->type->requiresOfficialSource() ? ' (required for this type)' : '' }}</span>@endforelse</dd>
<dt>Verification</dt><dd>{{ $entry->last_verified_at ? 'Checked against sources on '.$entry->last_verified_at->format('d M Y') : 'Not checked against sources yet' }}</dd>
<dt>Approval</dt><dd>@if($entry->review_status === 'approved')Approved for publishing @else Not approved @endif @if($entry->type->requiresOwnerApproval()) · Claims {{ $entry->owner_approved_at ? 'approved '.$entry->owner_approved_at->format('d M Y') : 'not approved' }}@endif</dd>
<dt>Missing before publishing</dt><dd>@if($blockers === [])<span>Nothing. It can be published from the editor.</span>@else<ul class="approval-list">@foreach($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>@endif</dd>
@if($entry->review_notes)<dt>Review notes</dt><dd class="approval-notes">{{ $entry->review_notes }}</dd>@endif
</dl>

<div class="approval-actions">
<a class="egh-button secondary" href="{{ route('admin.content.preview', $entry) }}" target="_blank" rel="noopener">Full preview</a>
<a class="egh-button secondary" href="{{ route('admin.content.edit', $entry) }}">Edit</a>
@if($entry->review_status !== 'approved')
@if($awaitingSources)
<p class="stu-muted approval-hint">Approval is locked until the sources are confirmed. Open each source link; if it says what the text says, set the review status to "Needs owner review" and the date checked in the editor.</p>
@else
<form method="POST" action="{{ route('admin.content.approvals.approve', $entry) }}" class="approval-form">@csrf
@if($reviewStatus !== '')<input type="hidden" name="review" value="{{ $reviewStatus }}">@endif
<label class="approval-check"><input type="checkbox" name="confirm_reviewed" value="1" required> I have read the English and Bengali text and it is accurate.</label>
@if($entry->type->requiresOfficialSource())<label class="approval-check"><input type="checkbox" name="confirm_sources" value="1" required> I have checked that the official sources say what the text says.</label>@endif
<button class="egh-button">Approve</button>
</form>
@endif
@endif
<form method="POST" action="{{ route('admin.content.approvals.reject', $entry) }}" class="approval-form">@csrf
@if($reviewStatus !== '')<input type="hidden" name="review" value="{{ $reviewStatus }}">@endif
<label class="approval-reason">What needs to change?<textarea name="reason" rows="2" minlength="5" maxlength="1000" required></textarea></label>
<button class="egh-button secondary">Request changes</button>
</form>
</div>
</article>
@empty
<div class="egh-card"><p>No unpublished content{{ $reviewStatus !== '' ? ' with this review status' : '' }}.</p></div>
@endforelse

<section class="egh-card admin-table-card" aria-labelledby="other-pending">
<h2 id="other-pending" class="approval-title">Other items waiting for an owner decision</h2>
<table class="stu-table"><thead><tr><th>Item</th><th>Count</th><th>What is needed</th><th>Open</th></tr></thead><tbody>
@foreach($otherPending as $item)<tr><td>{{ $item['label'] }}</td><td>{{ $item['count'] }}</td><td>{{ $item['note'] }}</td><td><a class="stu-link" href="{{ route($item['route']) }}">Manage</a></td></tr>@endforeach
</tbody></table>
</section>

<style>
.approval-filter{flex-wrap:wrap;gap:.5rem}
.approval-item{display:grid;gap:1rem}
.approval-head{display:flex;flex-wrap:wrap;justify-content:space-between;gap:.75rem;align-items:flex-start}
.approval-title{font-size:1.1rem;margin:0 0 .25rem}
.approval-badge{border-radius:999px;padding:.25rem .75rem;font-size:.85rem;font-weight:600;background:#eef2f7;color:#334155;white-space:nowrap}
.approval-badge.is-approved{background:#dcfce7;color:#14532d}
.approval-badge.is-needs_source_verification,.approval-badge.is-changes_requested{background:#fef3c7;color:#78350f}
.approval-badge.is-needs_owner_review,.approval-badge.is-needs_native_review{background:#dbeafe;color:#1e3a8a}
.approval-previews{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(min(100%,18rem),1fr))}
.approval-previews section{border:1px solid #e2e8f0;border-radius:.5rem;padding:.75rem;overflow-wrap:anywhere}
.approval-previews h3{font-size:.9rem;margin:0 0 .5rem;text-transform:uppercase;letter-spacing:.04em}
.approval-meta{display:grid;grid-template-columns:minmax(0,12rem) minmax(0,1fr);gap:.4rem 1rem;margin:0}
.approval-meta dt{font-weight:600}
.approval-meta dd{margin:0;overflow-wrap:anywhere}
.approval-notes{white-space:pre-line}
.approval-list{margin:0;padding-left:1.1rem}
.approval-actions{display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end}
.approval-form{display:flex;flex-wrap:wrap;gap:.5rem;align-items:flex-end;flex:1 1 18rem}
.approval-check{display:flex;gap:.5rem;align-items:flex-start;flex-basis:100%}
.approval-reason{display:grid;gap:.25rem;flex:1 1 14rem}
.approval-reason textarea{width:100%}
.approval-hint{flex:1 1 18rem;margin:0}
@media (max-width:640px){.approval-meta{grid-template-columns:1fr}.approval-meta dt{margin-top:.4rem}}
</style>
@endsection
