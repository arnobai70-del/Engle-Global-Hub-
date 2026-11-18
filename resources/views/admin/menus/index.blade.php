@extends('layouts.admin')
@section('title', 'Menus & Branding')
@section('content')
<x-admin.page-header title="Menus & Branding" description="Header and footer menus, homepage service tiles, the header button and the header tagline. Links can only point to existing pages or to https addresses, so menus cannot contain broken links." icon="M" eyebrow="Website CMS">
    <a class="egh-button" href="{{ route('admin.menus.create', ['location' => $location]) }}">Add item to {{ \App\Models\MenuItem::LOCATIONS[$location] }}</a>
</x-admin.page-header>
@if(session('status'))<div class="egho-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egho-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<nav class="egh-card stu-filter" aria-label="Menu locations">
    @foreach(\App\Models\MenuItem::LOCATIONS as $key => $label)
        <a class="egh-button {{ $key === $location ? '' : 'secondary' }}" href="{{ route('admin.menus.index', ['location' => $key]) }}" @if($key === $location) aria-current="page" @endif>{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
    @endforeach
</nav>

@if($tileWarnings !== [])
<div class="egho-alert is-warning" role="status" data-tile-warnings><strong>Homepage cards need attention</strong><ul class="admin-error-list">@foreach($items->whereIn('id', array_keys($tileWarnings)) as $tile)@foreach($tileWarnings[$tile->id] as $problem)<li>{{ $tile->label_en }}: {{ $problem }}</li>@endforeach @endforeach</ul></div>
@endif
<div class="egh-card admin-table-card">
    @if($items->isEmpty())
        <p>No items yet. The website uses its built-in {{ strtolower(\App\Models\MenuItem::LOCATIONS[$location]) }} until you add one. Run the menu seeder to start from the current menus.</p>
    @else
    <table class="stu-table">
        <thead><tr><th>Item</th><th>Links to</th><th>Shown to</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            @foreach([$item, ...$item->children] as $row)
                @php($isChild = $row->parent_id !== null)
                <tr data-menu-item="{{ $row->id }}">
                    <td>@if($isChild)<span aria-hidden="true">↳ </span>@endif<strong>{{ $row->label_en }}</strong>@if($row->label_bn)<br><span class="stu-muted" lang="bn">{{ $row->label_bn }}</span>@endif @if($row->icon)<br><span class="stu-muted">Icon: {{ $row->icon }}</span>@endif @foreach($tileWarnings[$row->id] ?? [] as $problem)<br><span class="admin-warning-text" style="color:#9a5b00">⚠ {{ $problem }}</span>@endforeach</td>
                    <td>
                        @if($row->target === null)<span class="stu-muted">Heading (no link)</span>
                        @elseif($url = $targets->url($row->target, $row->fragment))<a class="stu-link" href="{{ $url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit(str_replace(url('/'), '', $url) ?: '/', 48) }}</a>
                        @else<strong style="color:#c3121a">Broken target: hidden on the website</strong>@endif
                        @if($row->feature)<br><span class="stu-muted">Feature: {{ $row->feature }}</span>@endif
                    </td>
                    <td>{{ \App\Models\MenuItem::VISIBILITIES[$row->visibility] ?? $row->visibility }}@if($row->visibility === 'roles')<br><span class="stu-muted">{{ implode(', ', $row->roles ?? []) }}</span>@endif</td>
                    <td>{{ $row->is_active ? 'Shown' : 'Hidden' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.menus.move', $row) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="direction" value="up"><button class="egh-button secondary" type="submit" aria-label="Move {{ $row->label_en }} up">↑</button></form>
                        <form method="POST" action="{{ route('admin.menus.move', $row) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="direction" value="down"><button class="egh-button secondary" type="submit" aria-label="Move {{ $row->label_en }} down">↓</button></form>
                    </td>
                    <td>
                        <a class="stu-link" href="{{ route('admin.menus.edit', $row) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.menus.toggle', $row) }}" style="display:inline">@csrf @method('PATCH')<button class="egh-button secondary" type="submit">{{ $row->is_active ? 'Hide' : 'Show' }}</button></form>
                        <form method="POST" action="{{ route('admin.menus.destroy', $row) }}" style="display:inline" onsubmit="return confirm('Delete this menu item{{ $isChild ? '' : ' and its sub-items' }}?')">@csrf @method('DELETE')<button class="egh-button danger" type="submit">Delete</button></form>
                    </td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<section class="egh-card" aria-labelledby="branding-title">
    <h2 id="branding-title">Header tagline</h2>
    <p class="stu-muted">Shown under the logo. Leave empty to use the default ("Your Gateway to Study in Singapore", translated automatically). The logo, site name, footer text, contact details and social links are edited in <a class="stu-link" href="{{ route('admin.homepage.index') }}">Admin › Homepage</a>; the original logo is used unless you upload another. Any other label, button text or notice (including "Waiting for API" messages) can be changed in <a class="stu-link" href="{{ route('admin.translations.index') }}">Translations</a>.</p>
    <form method="POST" action="{{ route('admin.menus.branding') }}" class="stu-form-grid">@csrf @method('PATCH')
        <label>Tagline (English)<input name="header_tagline" maxlength="120" value="{{ old('header_tagline', $branding['header_tagline'] ?? '') }}" placeholder="Your Gateway to Study in Singapore"></label>
        <label>Tagline (Bengali)<input name="header_tagline_bn" lang="bn" maxlength="120" value="{{ old('header_tagline_bn', $branding['header_tagline_bn'] ?? '') }}" placeholder="সিঙ্গাপুরে পড়াশোনার আপনার প্রবেশদ্বার"></label>
        <div class="admin-form-actions"><button class="egh-button" type="submit">Save tagline</button></div>
    </form>
</section>
@endsection
