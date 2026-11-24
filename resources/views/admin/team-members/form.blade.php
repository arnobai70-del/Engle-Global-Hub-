@extends('layouts.admin')
@section('title', $member->exists ? 'Edit Team Member' : 'Add Team Member')
@section('content')
<x-admin.page-header :title="$member->exists ? 'Edit Team Member' : 'Add Team Member'" description="Publish only after the owner has verified the details." icon="T" eyebrow="Website CMS"><a class="egh-button secondary" href="{{ route('admin.team-members.index') }}">Back</a></x-admin.page-header>
<div class="egh-card">
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $member->exists ? route('admin.team-members.update', $member) : route('admin.team-members.store') }}">@csrf @if($member->exists) @method('PATCH') @endif
<div class="stu-form-grid">
<label>Name<input name="name" value="{{ old('name', $member->name) }}" maxlength="150" required></label>
<label>Role / designation<input name="role_title" value="{{ old('role_title', $member->role_title) }}" maxlength="150" required></label>
<label>Display order<input type="number" name="sort_order" min="0" value="{{ old('sort_order', $member->sort_order) }}" required></label>
<label>Photo (JPG, PNG or WebP, max 2 MB)<input id="team-photo-input" type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<div id="team-selected-photo-wrapper" class="stu-full" hidden>
<p>New photo preview (not saved yet)</p>
<img id="team-selected-photo-preview" alt="Selected team member photo" width="120" height="120" style="width:120px;height:120px;object-fit:cover;border-radius:12px;">
</div>
@if($member->photo_path)
<div class="stu-full">
<p>Current photo</p>
<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($member->photo_path) }}" alt="Current photo of {{ $member->name }}" width="120" height="120" style="width:120px;height:120px;object-fit:cover;border-radius:12px;">
</div>
@endif
@if($member->photo_path)<label><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>@endif
<label class="stu-full">Short biography<textarea name="bio" maxlength="3000">{{ old('bio', $member->bio) }}</textarea></label>
<label class="stu-full"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $member->is_published))> Published (details verified by the owner)</label>
<p class="stu-muted stu-full">Draft members never appear on About Us. Only publish after confirming the member's identity, name, title and photo (if provided). Published members without a photo display their initial.</p>
</div>
<div class="admin-form-actions"><button class="egh-button">Save</button><a class="egh-button secondary" href="{{ route('admin.team-members.index') }}">Cancel</a></div>
</form>
@if($member->exists)
<form method="POST" action="{{ route('admin.team-members.destroy', $member) }}" class="admin-danger-zone" onsubmit="return confirm('Delete this team member?')">@csrf @method('DELETE')<button class="egh-button danger">Delete</button></form>
@endif
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('team-photo-input');
    const wrapper = document.getElementById('team-selected-photo-wrapper');
    const preview = document.getElementById('team-selected-photo-preview');
    if (!input || !wrapper || !preview) return;
    let objectUrl = null;
    input.addEventListener('change', function () {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        const file = input.files && input.files[0];
        if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            wrapper.hidden = true;
            preview.removeAttribute('src');
            return;
        }
        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        wrapper.hidden = false;
    });
    window.addEventListener('pagehide', function () {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
    }, { once: true });
});
</script>
@endpush
@endsection
