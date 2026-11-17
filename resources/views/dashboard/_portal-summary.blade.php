@php
    $portalUser = auth()->user();
    $portalStudent = app(\App\Services\Consultancy\StudentPortal::class)->studentFor($portalUser);
    $portalApplications = $portalStudent?->applications()->withCount(['documents', 'messages'])->latest('updated_at')->get() ?? collect();
    $portalActive = $portalApplications->reject(fn ($application) => $application->status->isClosed());
    $portalLatest = $portalActive->first() ?? $portalApplications->first();
    $portalUpcoming = $portalStudent ? \App\Models\Appointment::query()->whereIn('student_application_id', $portalApplications->pluck('id'))->where('scheduled_at', '>=', now())->count() : 0;
    $portalSteps = ['Consultation', 'Admission', "Student's Pass", 'Completed'];
    $portalStep = $portalLatest ? match ($portalLatest->status->stage()) {
        'Consultation' => 0,
        'Admission', 'Action Required' => 1,
        "Student's Pass" => 2,
        default => 3,
    } : -1;
    $portalIcon = static fn (string $path): string => '<svg viewBox="0 0 24 24" aria-hidden="true">'.$path.'</svg>';
@endphp
<section class="sg-card sg-portal-top" aria-labelledby="portal-welcome">
    <div>
        <h1 id="portal-welcome">{{ __('Welcome, :name', ['name' => $portalUser->name]) }}</h1>
        <p>{{ __('Track your application, documents and next steps here.') }}</p>
    </div>
    <a href="{{ route('consultancy.singapore') }}#consultation" class="sg-btn sg-btn-red sg-btn-sm">{{ __('Request a free consultation') }}</a>
</section>
@php
    $portalAccountType = $portalUser->account_type;
    $portalPartner = $portalAccountType === 'agent'
        ? rescue(fn () => \App\Models\Agent::query()->where('email', strtolower((string) $portalUser->email))->first(), null, false)
        : null;
    $portalNext = match (true) {
        $portalAccountType === 'agent' && $portalPartner === null => ['icon' => 'group', 'title' => __('Finish your partner application'), 'text' => __('Tell us about your agency. The Agent Portal opens after our team approves your application.'), 'url' => route('b2b.register'), 'label' => __('Apply as a partner')],
        $portalPartner?->status === \App\Models\Agent::STATUS_ACTIVE && $portalUser->hasVerifiedEmail() => ['icon' => 'group', 'title' => __('Your Agent Portal is ready'), 'text' => __('Refer students and travel clients and follow every referral.'), 'url' => route('agent.index'), 'label' => __('Open Agent Portal')],
        $portalAccountType === 'agent' && $portalPartner?->status === \App\Models\Agent::STATUS_PENDING =>['icon' => 'clock', 'title' => __('Your partner application is being reviewed'), 'text' => __('We will email you when it is approved. You can still send enquiries in the meantime.'), 'url' => route('support'), 'label' => __('Contact Us')],
        $portalAccountType === 'student' && $portalStudent === null => ['icon' => 'cap', 'title' => __('Your next step: a free consultation'), 'text' => __('Book a consultation. When your consultant opens your file, your application, documents and messages appear here.'), 'url' => route('consultancy.singapore').'#consultation', 'label' => __('Book a free consultation')],
        $portalAccountType === 'traveller' => ['icon' => 'plane', 'title' => __('Plan your next trip'), 'text' => __('Send a visa, air ticket, hotel or tour enquiry. Replies and updates appear under My Enquiries.'), 'url' => route('travel-services.index'), 'label' => __('Browse travel services')],
        default => null,
    };
@endphp
@if($portalNext)
<section class="sg-card sg-next-step" aria-label="{{ __('Next step') }}">
    <x-site.art :name="$portalNext['icon']" />
    <div><strong>{{ $portalNext['title'] }}</strong><p>{{ $portalNext['text'] }}</p></div>
    <a class="sg-btn sg-btn-red sg-btn-sm" href="{{ $portalNext['url'] }}">{{ $portalNext['label'] }}</a>
</section>
@endif
<section class="sg-stats" aria-label="{{ __('Summary') }}">
    <a class="sg-card sg-stat" href="{{ route('student.applications.index') }}"><span class="sg-icon sg-icon-green">{!! $portalIcon('<path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>') !!}</span><span><strong>{{ $portalActive->count() }}</strong><span>{{ __('Active applications') }}</span></span></a>
    <a class="sg-card sg-stat" href="{{ route('student.applications.index') }}"><span class="sg-icon sg-icon-navy">{!! $portalIcon('<path d="M4 4h10l6 6v10H4z"/><path d="M14 4v6h6"/>') !!}</span><span><strong>{{ $portalApplications->sum('documents_count') }}</strong><span>{{ __('Documents') }}</span></span></a>
    <a class="sg-card sg-stat" href="{{ route('student.applications.index') }}"><span class="sg-icon">{!! $portalIcon('<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>') !!}</span><span><strong>{{ $portalUpcoming }}</strong><span>{{ __('Upcoming appointments') }}</span></span></a>
    <a class="sg-card sg-stat" href="{{ route('student.applications.index') }}"><span class="sg-icon sg-icon-navy">{!! $portalIcon('<path d="M4 5h16v11H8l-4 4z"/>') !!}</span><span><strong>{{ $portalApplications->sum('messages_count') }}</strong><span>{{ __('Messages') }}</span></span></a>
</section>
<section class="sg-card" style="padding:1.4rem 1.6rem" aria-labelledby="portal-progress">
    <h2 id="portal-progress" style="font-size:1.1rem;margin:0">{{ __('Application Progress') }}</h2>
    @if($portalLatest)
        <p style="margin:.4rem 0 0;color:var(--sg-muted)">{{ $portalLatest->course?->name ?? $portalLatest->course_name ?? __('Study application') }} · {{ __($portalLatest->status->label()) }}</p>
        <ol class="sg-progress">
            @foreach($portalSteps as $index => $step)
                <li @class(['is-done' => $index < $portalStep || ($index === 3 && $portalStep === 3), 'is-current' => $index === $portalStep])>{{ __($step) }}</li>
            @endforeach
        </ol>
        <a class="sg-link-arrow" href="{{ route('student.applications.show', $portalLatest) }}">{{ __('View details') }}</a>
    @else
        <p style="margin:.4rem 0 0;color:var(--sg-muted)">{{ __('Your student profile has no applications yet. Your consultant will open one after your consultation.') }}</p>
    @endif
</section>
