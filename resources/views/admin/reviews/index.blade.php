@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')
<x-admin.page-header title="Customer Reviews" description="Reviews written by signed-in customers. Approve only genuine, lawful reviews; never edit their wording." icon="R" eyebrow="Website CMS" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card stu-filter">@foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)<a class="egh-button {{ $status === $value ? '' : 'secondary' }}" href="{{ route('admin.reviews.index', ['status' => $value]) }}">{{ $label }}</a> @endforeach</div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Submitted</th><th>Customer</th><th>Rating</th><th>Review</th><th>Action</th></tr></thead><tbody>
@forelse($reviews as $review)<tr><td>{{ $review->created_at->format('Y-m-d') }}</td><td>{{ $review->display_name }}<br><span class="stu-muted">{{ $review->user->email }}</span></td><td>{{ $review->rating }}/5</td><td><strong>{{ $review->title }}</strong><br><span class="stu-notes">{{ $review->body }}</span></td>
<td>@can('content.manage')@if($review->status !== 'approved')<form method="POST" action="{{ route('admin.reviews.update', $review) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="egh-button">Approve</button></form>@endif @if($review->status !== 'rejected')<form method="POST" action="{{ route('admin.reviews.update', $review) }}" style="display:inline">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="egh-button danger">Reject</button></form>@endif @endcan</td></tr>@empty<tr><td colspan="5">No {{ $status }} reviews.</td></tr>@endforelse
</tbody></table></div>@if($reviews->hasPages())<div class="admin-pagination">{{ $reviews->links() }}</div>@endif
@endsection
