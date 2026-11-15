@extends('layouts.admin')
@section('title', 'Offers')
@section('content')
<x-admin.page-header title="Offers & Promotions" description="Only published offers within their validity dates appear on the website." icon="O" eyebrow="Website CMS">
@can('content.manage')<a class="egh-button" href="{{ route('admin.offers.create') }}">Add Offer</a>@endcan
</x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Title</th><th>Service</th><th>Valid</th><th>Visibility</th><th>Action</th></tr></thead><tbody>
@forelse($offers as $offer)<tr><td>{{ $offer->title }}</td><td>{{ $offer->service?->label() ?? 'Any' }}</td><td>{{ $offer->valid_from?->format('Y-m-d') ?? 'Now' }} → {{ $offer->valid_until?->format('Y-m-d') ?? 'Open-ended' }}</td><td>{{ $offer->is_published ? 'Published' : 'Draft' }}</td><td>@can('content.manage')<a class="stu-link" href="{{ route('admin.offers.edit', $offer) }}">Edit</a>@endcan</td></tr>@empty<tr><td colspan="5">No offers yet.</td></tr>@endforelse
</tbody></table></div>@if($offers->hasPages())<div class="admin-pagination">{{ $offers->links() }}</div>@endif
@endsection
