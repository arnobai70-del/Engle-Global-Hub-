@extends('layouts.admin')
@section('title', $destination->exists ? 'Edit '.$destination->name : 'Add Destination')
@section('content')
<x-admin.page-header :title="$destination->exists ? $destination->name : 'Add Destination'" description="Write only verified information. A summary is required before publishing." icon="D" eyebrow="Website CMS"><a class="egh-button secondary" href="{{ route('admin.destinations.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card">
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $destination->exists ? route('admin.destinations.update', $destination) : route('admin.destinations.store') }}">@csrf @if($destination->exists) @method('PATCH') @endif
<div class="stu-form-grid">
<label>Name<input name="name" value="{{ old('name', $destination->name) }}" maxlength="150" required></label>
<label>URL slug (letters, numbers, hyphens)<input name="slug" value="{{ old('slug', $destination->slug) }}" maxlength="160" placeholder="generated from the name"></label>
<label>Country<select name="country_id" required>@foreach($countries as $country)<option value="{{ $country->id }}" @selected((string) old('country_id', $destination->country_id) === (string) $country->id)>{{ $country->name }}</option>@endforeach</select></label>
<label>Name in Bengali (optional)<input name="name_bn" lang="bn" value="{{ old('name_bn', $destination->name_bn) }}" maxlength="150"></label>
<label>Region / division<input name="region" value="{{ old('region', $destination->region) }}" maxlength="150"></label>
<label>Category (filter on Explore Bangladesh)<select name="category"><option value="">No category</option>@foreach(\App\Models\Destination::CATEGORIES as $value => $label)<option value="{{ $value }}" @selected(old('category', $destination->category) === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Display order<input type="number" name="sort_order" min="0" value="{{ old('sort_order', $destination->sort_order) }}" required></label>
<label class="stu-full">Summary (shown on listing cards)<textarea name="summary" maxlength="500">{{ old('summary', $destination->summary) }}</textarea></label>
<label class="stu-full">Summary in Bengali (optional)<textarea name="summary_bn" lang="bn" maxlength="500">{{ old('summary_bn', $destination->summary_bn) }}</textarea></label>
<label class="stu-full">Description (plain text; blank lines start new paragraphs)<textarea name="description" maxlength="20000" rows="10">{{ old('description', $destination->description) }}</textarea></label>
<label class="stu-full">Description in Bengali (optional)<textarea name="description_bn" lang="bn" maxlength="20000" rows="6">{{ old('description_bn', $destination->description_bn) }}</textarea></label>
<label>Best time to visit<input name="best_time_to_visit" value="{{ old('best_time_to_visit', $destination->best_time_to_visit) }}" maxlength="150" placeholder="For example: November to February"></label>
<label class="stu-full">How to get there<textarea name="how_to_get_there" maxlength="5000" rows="3">{{ old('how_to_get_there', $destination->how_to_get_there) }}</textarea></label>
<label class="stu-full">Travel tips (permits, safety, what to bring)<textarea name="travel_tips" maxlength="5000" rows="3">{{ old('travel_tips', $destination->travel_tips) }}</textarea></label>
<label>Latitude (for the map, optional)<input name="latitude" inputmode="decimal" value="{{ old('latitude', $destination->latitude) }}" placeholder="23.8103"></label>
<label>Longitude<input name="longitude" inputmode="decimal" value="{{ old('longitude', $destination->longitude) }}" placeholder="90.4125"></label>
<label>SEO title (max 70)<input name="meta_title" value="{{ old('meta_title', $destination->meta_title) }}" maxlength="70"></label>
<label>SEO description (max 170)<input name="meta_description" value="{{ old('meta_description', $destination->meta_description) }}" maxlength="170"></label>
<label>Cover image (JPG, PNG, WebP; max 4 MB)<input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label>
<label>Cover image description (alt text)<input name="cover_image_alt" value="{{ old('cover_image_alt', $destination->cover_image_alt) }}" maxlength="200"></label>
@if($destination->exists)
<label>Add gallery image<input type="file" name="gallery_image" accept="image/jpeg,image/png,image/webp"></label>
<label>Gallery image description (alt text)<input name="gallery_alt" value="{{ old('gallery_alt') }}" maxlength="200"></label>
@endif
@if($destination->exists && isset($otherDestinations))<input type="hidden" name="recommendations_submitted" value="1"><label>Places you may like (shown on this page)<select name="recommended_ids[]" multiple size="4">@foreach($otherDestinations as $other)<option value="{{ $other->id }}" @selected(collect(old('recommended_ids', $destination->recommendations->pluck('id')->all()))->contains($other->id))>{{ $other->name }}</option>@endforeach</select></label>@endif
<label class="stu-full"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $destination->is_featured))> Featured (shown first; its cover photo can be the Explore Bangladesh banner)</label>
<label class="stu-full"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $destination->is_published))> Published on the website</label>
</div>
<div class="admin-form-actions"><button class="egh-button">Save</button></div>
</form>
@if($destination->exists && $destination->images->isNotEmpty())
<h2>Gallery</h2>
<ul>@foreach($destination->images as $image)<li><a class="stu-link" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}" target="_blank" rel="noopener">{{ $image->alt_text }}</a>
<form method="POST" action="{{ route('admin.destinations.images.destroy', [$destination, $image]) }}" style="display:inline">@csrf @method('DELETE')<button class="egh-button danger">Remove</button></form></li>@endforeach</ul>
@endif
@if($destination->exists)
<section id="listings" aria-labelledby="listings-title" style="margin-top:2rem">
<h2 id="listings-title">Hotels, restaurants and experiences</h2>
<p class="stu-muted">Shown on the public page in "Where to stay", "Where to eat" and "Things to do". Add only places you have verified. Do not enter prices, phone numbers or ratings here; link to the official website instead. A block stays hidden until it has a published listing.</p>
<table class="stu-table"><thead><tr><th>Type</th><th>Name</th><th>Area</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($destination->listings as $listing)<tr data-listing="{{ $listing->id }}"><td>{{ ['hotel' => 'Hotel', 'restaurant' => 'Restaurant', 'experience' => 'Experience'][$listing->type] ?? $listing->type }}</td><td>{{ $listing->name }}</td><td>{{ $listing->area ?: '—' }}</td><td>{{ $listing->is_published ? 'Published' : 'Draft' }}</td><td><a class="stu-link" href="{{ route('admin.destinations.listings.edit', [$destination, $listing]) }}">Edit</a></td></tr>@empty<tr><td colspan="5">No listings yet.</td></tr>@endforelse
</tbody></table>
<h3>Add listing</h3>
<form method="POST" action="{{ route('admin.destinations.listings.store', $destination) }}">@csrf
@include('admin.destinations._listing-fields', ['listing' => null])
<div class="admin-form-actions"><button class="egh-button">Add listing</button></div>
</form>
</section>
<form method="POST" action="{{ route('admin.destinations.destroy', $destination) }}" class="admin-danger-zone" onsubmit="return confirm('Delete this destination and its images?')">@csrf @method('DELETE')<button class="egh-button danger">Delete Destination</button></form>
@endif
</div>
@endsection
