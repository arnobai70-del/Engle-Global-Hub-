@extends('layouts.admin')
@section('title', 'Manage Destinations')
@section('content')
<x-admin.page-header title="Manage Destinations" description="Explore Bangladesh and other destination pages. Only published destinations appear on the website." icon="D" eyebrow="Website CMS">
@canany(['master-data.manage', 'content.manage'])<a class="egh-button" href="{{ route('admin.destinations.create') }}">Add Destination</a>@endcanany
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<form method="GET" class="egh-card stu-filter"><input name="search" value="{{ $search }}" placeholder="Destination name" aria-label="Search destinations"><button class="egh-button">Filter</button></form>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Order</th><th>Name</th><th>Country</th><th>Photos</th><th>Visibility</th><th>Public page</th><th>Action</th></tr></thead><tbody>
@forelse($destinations as $destination)<tr><td>{{ $destination->sort_order }}</td><td>{{ $destination->name }}</td><td>{{ $destination->country->name }}</td><td>{{ ($destination->cover_image_path ? 1 : 0) + $destination->images_count }}</td><td>{{ $destination->is_published ? 'Published' : 'Draft' }}</td><td>@if($destination->is_published && $destination->country->name === 'Bangladesh')<a class="stu-link" href="{{ route('explore-bangladesh.show', $destination->slug) }}" target="_blank" rel="noopener">View</a>@else — @endif</td><td>@canany(['master-data.manage', 'content.manage'])<a class="stu-link" href="{{ route('admin.destinations.edit', $destination) }}">Edit</a>@endcanany</td></tr>@empty<tr><td colspan="7">No destinations yet.</td></tr>@endforelse
</tbody></table></div>@if($destinations->hasPages())<div class="admin-pagination">{{ $destinations->links() }}</div>@endif
@endsection
