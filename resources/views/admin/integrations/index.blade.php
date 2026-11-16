@extends('layouts.admin')
@section('title', 'API & Integrations')
@section('content')
<x-admin.page-header title="API & Integrations" description="Status of every third-party connection, the module it serves, and the manual process used when it is not connected." icon="I" eyebrow="API & Integrations" />
<div class="egh-card">
<p>Credentials are kept in the server's environment variables, never in the database or on this page. This page shows only whether required values are present. To connect a provider, set the listed variables on the server, then run <code>php artisan config:cache</code> and <code>php artisan app:production-readiness</code>.</p>
<p>Adding a key for a provider that has no adapter does nothing: a developer must build and test an adapter first. No incoming provider webhooks are used yet, so there are no webhook secrets to configure.</p>
</div>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if(session('test_error'))<div class="egh-alert is-error" role="alert">{{ session('test_error') }}</div>@endif
<section class="egh-card" aria-labelledby="public-status"><h2 id="public-status">Public status on the website</h2><p>What visitors see on each API-dependent feature. <strong>Live Provider</strong> appears only after a passing connection test for the current configuration; changing a key or provider returns it to <strong>Waiting for API</strong> until it is tested again.</p><table class="stu-table"><thead><tr><th>Feature</th><th>Shown on the website</th></tr></thead><tbody>@foreach($publicStatuses as $key => $item)<tr data-capability="{{ $key }}"><td>{{ $item['label'] }}</td><td><strong>{{ strtoupper($item['state_label']) }}</strong></td></tr>@endforeach</tbody></table></section>
@foreach($integrations as $group => $items)
<div class="egh-card admin-table-card">
<h2>{{ $group }}</h2>
<table class="stu-table"><thead><tr><th>Integration</th><th>Module</th><th>Provider</th><th>Status</th><th>Environment variables</th><th>Without it</th></tr></thead><tbody>
@foreach($items as $item)<tr>
<td><strong>{{ $item['name'] }}</strong>@if($item['notes'])<br><span class="stu-muted">{{ $item['notes'] }}</span>@endif</td>
<td>{{ $item['module'] }}</td>
<td>{{ $item['provider'] }}</td>
<td>{{ $item['status_label'] }}@if($item['missing'] !== [])<br><span class="stu-muted">Missing: {{ implode(', ', $item['missing']) }}</span>@endif @if(in_array($item['key'], $testable, true))<form method="POST" action="{{ route('admin.integrations.test', $item['key']) }}">@csrf<button class="egh-button secondary">Test connection</button></form>@endif</td>
<td>{{ $item['env'] === [] ? '—' : implode(', ', $item['env']) }}</td>
<td>@if($item['fallback']){{ $item['fallback'] }}@if($item['fallback_url']) — <a class="stu-link" href="{{ $item['fallback_url'] }}">open</a>@endif @else — @endif</td>
</tr>@endforeach
</tbody></table>
</div>
@endforeach
@endsection
