@extends('layouts.admin')
@section('title', 'Services & Destinations')
@section('content')
<x-admin.page-header title="Services & Destinations" description="Services offered and the destination countries for each visa service." icon="C" eyebrow="Travel & Visa" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="egh-card">
<h2>Services offered</h2>
<p>From the business requirements document. Listing a service here does not make it bookable online; online booking depends on a connected provider.</p>
<ul>@foreach($services as $service)<li>{{ $service->label() }}</li>@endforeach</ul>
</div>

@foreach($services as $service)
@if($service->hasDestinations())
<div class="egh-card admin-table-card">
<h2>{{ $service->label() }} destinations</h2>
@if($service === \App\Enums\BusinessService::StudentVisa)<p>Each active country can have a homepage study card. Newly added countries get a homepage card linking to the enquiry form until its full study page is published. Existing countries (including Japan and Singapore) can be enabled or hidden here. No country is deleted by hiding a card.</p>@endif
<table class="stu-table"><thead><tr><th>Order</th><th>Country</th><th>Status</th><th>Homepage</th><th>Action</th></tr></thead><tbody>
@forelse($destinations->get($service->value, collect()) as $destination)<tr>
<td>{{ $destination->sort_order }}</td><td>{{ $destination->country->name }} ({{ $destination->country->iso2 }})</td><td>{{ $destination->is_active ? 'Active' : 'Hidden' }}</td>
<td>@if($service === \App\Enums\BusinessService::StudentVisa){{ $destination->show_on_homepage && $destination->is_active ? 'Shown' : 'Hidden' }}@else—@endif</td>
<td>@can('master-data.manage')
@if($service === \App\Enums\BusinessService::StudentVisa)
<form method="POST" action="{{ route('admin.service-destinations.homepage', $destination) }}" style="display:inline-flex;gap:6px">
@csrf @method('PATCH')
<input type="hidden" name="show_on_homepage" value="{{ $destination->show_on_homepage ? 0 : 1 }}">
<button class="egh-button secondary" type="submit" @disabled(!$destination->is_active)>{{ $destination->show_on_homepage ? 'Hide home card' : 'Show home card' }}</button>
</form>
@endif
<form method="POST" action="{{ route('admin.service-destinations.update', $destination) }}" style="display:inline-flex;gap:6px">@csrf @method('PATCH')<input type="number" name="sort_order" value="{{ $destination->sort_order }}" min="0" style="width:70px" aria-label="Order"><input type="hidden" name="is_active" value="{{ $destination->is_active ? 0 : 1 }}"><button class="egh-button secondary">{{ $destination->is_active ? 'Hide' : 'Activate' }}</button></form>
<form method="POST" action="{{ route('admin.service-destinations.destroy', $destination) }}" style="display:inline" onsubmit="return confirm('Remove this destination?')">@csrf @method('DELETE')<button class="egh-button danger">Remove</button></form>
@endcan</td></tr>@empty<tr><td colspan="5">No destinations yet.</td></tr>@endforelse
</tbody></table>
@can('master-data.manage')
<form method="POST" action="{{ route('admin.service-destinations.store') }}" class="stu-filter">@csrf<input type="hidden" name="service" value="{{ $service->value }}">
<select name="country_id" aria-label="Country to add" required><option value="">Add a country…</option>@foreach($countries as $country)<option value="{{ $country->id }}">{{ $country->name }}</option>@endforeach</select>
<button class="egh-button">Add</button></form>
@endcan
</div>
@endif
@endforeach
@endsection
