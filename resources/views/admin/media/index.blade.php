@extends('layouts.admin')
@section('title', 'Media Library')
@section('content')
<x-admin.page-header title="Media Library" description="Website images. Uploads start as drafts; office gallery images appear on the About page when published." icon="M" eyebrow="Website CMS" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@can('content.manage')
<div class="egh-card"><h2>Upload image</h2>
<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">@csrf
<div class="stu-form-grid">
<label>Image (JPG, PNG, WebP; max 4 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label>
<label>Collection<select name="collection">@foreach(\App\Models\MediaAsset::COLLECTIONS as $value => $label)<option value="{{ $value }}" @selected($collection === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Description for screen readers and search engines (alt text)<input name="alt_text" maxlength="200" required></label>
<label>Caption (optional)<input name="caption" maxlength="300"></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Upload</button></div></form></div>
@endcan
<div class="egh-card stu-filter"><a class="egh-button {{ $collection === '' ? '' : 'secondary' }}" href="{{ route('admin.media.index') }}">All</a>@foreach(\App\Models\MediaAsset::COLLECTIONS as $value => $label) <a class="egh-button {{ $collection === $value ? '' : 'secondary' }}" href="{{ route('admin.media.index', ['collection' => $value]) }}">{{ $label }}</a>@endforeach</div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Image</th><th>Details</th><th>Public URL</th></tr></thead><tbody>
@forelse($assets as $asset)<tr>
<td><img src="{{ $asset->url() }}" alt="{{ $asset->alt_text }}" loading="lazy" style="width:120px;height:auto;border-radius:8px"><br><span class="stu-muted">{{ $asset->width }}×{{ $asset->height }} · {{ number_format($asset->size_bytes / 1024) }} KB</span></td>
<td>@can('content.manage')<form method="POST" action="{{ route('admin.media.update', $asset) }}" style="display:grid;gap:6px;min-width:220px">@csrf @method('PATCH')
<input name="alt_text" value="{{ $asset->alt_text }}" maxlength="200" required aria-label="Alt text">
<input name="caption" value="{{ $asset->caption }}" maxlength="300" placeholder="Caption" aria-label="Caption">
<select name="collection" aria-label="Collection">@foreach(\App\Models\MediaAsset::COLLECTIONS as $value => $label)<option value="{{ $value }}" @selected($asset->collection === $value)>{{ $label }}</option>@endforeach</select>
<input type="number" name="sort_order" value="{{ $asset->sort_order }}" min="0" aria-label="Order">
<label><input type="checkbox" name="is_published" value="1" @checked($asset->is_published)> Published</label>
<button class="egh-button secondary">Save</button></form>
<form method="POST" action="{{ route('admin.media.destroy', $asset) }}" onsubmit="return confirm('Delete this image?')">@csrf @method('DELETE')<button class="egh-button danger">Delete</button></form>
@else{{ $asset->alt_text }}@endcan</td>
<td><input value="{{ $asset->url() }}" readonly aria-label="Image URL" onclick="this.select()" style="width:100%"></td>
</tr>@empty<tr><td colspan="3">No images yet.</td></tr>@endforelse
</tbody></table></div>@if($assets->hasPages())<div class="admin-pagination">{{ $assets->links() }}</div>@endif
@endsection
