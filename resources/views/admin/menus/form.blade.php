@extends('layouts.admin')
@section('title', $item->exists ? 'Edit menu item' : 'Add menu item')
@section('content')
@php
    $currentType = old('link_type', $item->target === null ? ($item->exists ? 'none' : 'route') : (str_starts_with((string) $item->target, 'url:') ? 'url' : 'route'));
    $nested = in_array($item->location, \App\Models\MenuItem::NESTED_LOCATIONS, true);
@endphp
<x-admin.page-header :title="$item->exists ? 'Edit menu item' : 'Add menu item'" :description="\App\Models\MenuItem::LOCATIONS[$item->location]" icon="M" eyebrow="Menus & Branding"><a class="egh-button secondary" href="{{ route('admin.menus.index', ['location' => $item->location]) }}">Back</a></x-admin.page-header>
@if($errors->any())<div class="egho-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $item->exists ? route('admin.menus.update', $item) : route('admin.menus.store') }}" class="egh-card">@csrf
    @if($item->exists) @method('PATCH') @else <input type="hidden" name="location" value="{{ $item->location }}"> @endif
    <div class="stu-form-grid">
        <label>Label (English)<input name="label_en" required maxlength="80" value="{{ old('label_en', $item->label_en) }}"></label>
        <label>Label (Bengali)<input name="label_bn" lang="bn" maxlength="80" value="{{ old('label_bn', $item->label_bn) }}"></label>
        @if($nested)
        <label>Parent (dropdown or footer column)<select name="parent_id"><option value="">None — top level</option>@foreach($parents as $parent)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $item->parent_id) === (string) $parent->id)>{{ $parent->label_en }}</option>@endforeach</select></label>
        @endif
        <label>Link type<select name="link_type">
            <option value="route" @selected($currentType === 'route')>Page on this website</option>
            <option value="url" @selected($currentType === 'url')>External https link</option>
            @if($nested)<option value="none" @selected($currentType === 'none')>Heading without a link (top level only)</option>@endif
        </select></label>
        <label>Page on this website<select name="route_target"><option value="">Choose a page</option>@foreach($targetOptions as $group => $options)<optgroup label="{{ $group }}">@foreach($options as $value => $label)<option value="{{ $value }}" @selected(old('route_target', $item->target) === $value)>{{ $label }}</option>@endforeach</optgroup>@endforeach</select></label>
        <label>External link (https only)<input name="external_url" type="url" maxlength="290" placeholder="https://" value="{{ old('external_url', str_starts_with((string) $item->target, 'url:') ? substr((string) $item->target, 4) : '') }}"></label>
        <label>Section anchor (optional, e.g. consultation)<input name="fragment" maxlength="60" pattern="[A-Za-z0-9_-]+" value="{{ old('fragment', $item->fragment) }}"></label>
        @if($item->location === 'home_tiles')
        <label>Icon<select name="icon" required><option value="">Choose an icon</option>@foreach(\App\Models\MenuItem::ICONS as $icon)<option value="{{ $icon }}" @selected(old('icon', $item->icon) === $icon)>{{ $icon }}</option>@endforeach</select></label>
        @endif
        <label>Hide when this feature is switched off (optional)<select name="feature"><option value="">No feature</option>@foreach($features as $feature)<option value="{{ $feature }}" @selected(old('feature', $item->feature) === $feature)>{{ $feature }}</option>@endforeach</select></label>
        <label>Shown to<select name="visibility">@foreach(\App\Models\MenuItem::VISIBILITIES as $value => $label)<option value="{{ $value }}" @selected(old('visibility', $item->visibility) === $value)>{{ $label }}</option>@endforeach</select></label>
        <fieldset class="stu-full"><legend>Roles (when "Selected roles only")</legend>@foreach($roles as $role)<label style="display:inline-flex;gap:6px;margin-right:14px"><input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, old('roles', $item->roles ?? []), true))> {{ $role }}</label>@endforeach</fieldset>
        <label>Display order<input type="number" name="sort_order" min="0" max="65535" required value="{{ old('sort_order', $item->sort_order) }}"></label>
        <label style="display:flex;gap:8px;align-items:center"><input type="hidden" name="open_in_new_tab" value="0"><input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $item->open_in_new_tab))> Open external links in a new tab</label>
        <label style="display:flex;gap:8px;align-items:center"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Shown on the website</label>
    </div>
    <div class="admin-form-actions"><button class="egh-button" type="submit">Save</button><a class="egh-button secondary" href="{{ route('admin.menus.index', ['location' => $item->location]) }}">Cancel</a></div>
</form>
@endsection
