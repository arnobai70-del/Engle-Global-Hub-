@php
    $kpiUser = auth()->user();
    $kpis = array_values(array_filter([
        $kpiUser->can('student-enquiries.view') ? ['value' => \App\Models\StudentEnquiry::query()->where('status', \App\Models\StudentEnquiry::STATUS_NEW)->count(), 'label' => 'New student enquiries', 'href' => route('admin.student-enquiries.index')] : null,
        $kpiUser->can('student-applications.view') ? ['value' => \App\Models\StudentApplication::query()->whereNotIn('status', ['completed', 'rejected', 'withdrawn'])->count(), 'label' => 'Active applications', 'href' => route('admin.student-applications.index')] : null,
        $kpiUser->can('users.view') ? ['value' => \App\Models\User::query()->count(), 'label' => 'Registered users', 'href' => route('admin.users.index')] : null,
        $kpiUser->can('agents.view') ? ['value' => \App\Models\Agent::query()->where('status', \App\Models\Agent::STATUS_PENDING)->count(), 'label' => 'Agents awaiting approval', 'href' => route('admin.agents.index')] : null,
        $kpiUser->can('content.view') ? ['value' => \App\Models\ContentEntry::query()->where('status', \App\Models\ContentEntry::STATUS_DRAFT)->count(), 'label' => 'Draft content', 'href' => route('admin.content.index', ['status' => 'draft'])] : null,
        $kpiUser->can('content.approve') ? ['value' => \App\Models\ContentEntry::query()->where('review_status', 'needs_owner_review')->count(), 'label' => 'Waiting for owner review', 'href' => route('admin.content.approvals')] : null,
    ]));
@endphp
@if($kpis !== [])
<section class="sga-kpis" aria-label="Key figures">
    @foreach($kpis as $kpi)
        <a class="sga-kpi" href="{{ $kpi['href'] }}"><strong>{{ number_format($kpi['value']) }}</strong><span>{{ $kpi['label'] }}</span></a>
    @endforeach
</section>
@endif
