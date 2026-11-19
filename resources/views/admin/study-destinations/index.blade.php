@extends('layouts.admin')
@section('title', 'Study destinations')
@section('content')
<x-admin.page-header title="Study destinations" description="The /study-in-… pages for every study country except Malaysia and Singapore, which keep their own pages." icon="S" eyebrow="Website CMS" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card">
    <p>A page becomes public only when it is <strong>published</strong> and has an introduction, at least one entry requirement, at least one student visa step and at least one official source you have checked. Until then the menu keeps opening the Student Visa enquiry form for that country, so nothing breaks.</p>
    <p>Enter only facts you have confirmed on the official website and add the link as the source of each fact. The suggested sources are marked <em>verify before publishing</em>: open them, confirm they are the right official page, then switch that off.</p>
</div>
<div class="egh-card admin-table-card">
<table class="stu-table">
    <thead><tr><th>Order</th><th>Country</th><th>Page</th><th>Status</th><th>Still needed</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($destinations as $destination)
        @php($blockers = $destination->publishBlockers())
        <tr data-destination="{{ $destination->slug }}">
            <td>
                @can('content.manage')
                    @if(isset($order[$destination->country_id]))
                    <form method="POST" action="{{ route('admin.study-destinations.move', $destination) }}" class="stu-inline-form">@csrf @method('PATCH')<button class="egh-button secondary" name="direction" value="up" aria-label="Move {{ $destination->country?->name }} up">↑</button><button class="egh-button secondary" name="direction" value="down" aria-label="Move {{ $destination->country?->name }} down">↓</button></form>
                    @else
                    <span class="stu-muted">Not in Student Visa</span>
                    @endif
                @else
                    {{ ($order[$destination->country_id] ?? null) !== null ? $order[$destination->country_id] + 1 : '—' }}
                @endif
            </td>
            <td><strong>{{ $destination->country?->name }}</strong>@if($destination->name_bn)<br><span class="stu-muted" lang="bn">{{ $destination->name_bn }}</span>@endif</td>
            <td>/study-in-{{ $destination->slug }}@if($destination->isPublic())<br><a class="stu-link" href="{{ route('study-destinations.show', $destination->slug) }}" target="_blank" rel="noopener">View</a>@endif</td>
            <td>{{ $destination->isPublic() ? 'Public' : ($destination->status === 'published' ? 'Published, but hidden (incomplete)' : 'Draft') }}</td>
            <td>@if($blockers === [])—@else<ul class="admin-error-list">@foreach($blockers as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>@endif</td>
            <td>@can('content.manage')<a class="stu-link" href="{{ route('admin.study-destinations.edit', $destination) }}">Edit</a>@endcan</td>
        </tr>
    @empty
        <tr><td colspan="6">No study destinations yet. Run the production seeder to add the draft pages.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
