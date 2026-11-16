@extends('layouts.site')
@section('title', 'Application '.$application->reference)
@section('robots', 'noindex,nofollow')
@section('body_class', 'public-page-body egho-page-body')
@section('content')
<x-site.portal active="applications" kind="student">
<main class="egho-page">
    <div class="egho-shell">
        @if(session('status'))<div class="egho-result-summary" role="status"><strong>{{ session('status') }}</strong></div>@endif
        @if($errors->any())<div class="egho-result-summary" role="alert"><strong>{{ __('Please correct the following:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

        <div class="egho-contact-grid">
            <section class="egho-contact-card" aria-labelledby="application-summary">
                <h1 id="application-summary">Application {{ $application->reference }}</h1>
                <ol aria-label="Application progress" style="display:flex;flex-wrap:wrap;gap:8px;list-style:none;padding:0;margin:0 0 16px">
                    @foreach($progress as $step)
                        <li style="padding:4px 10px;border-radius:999px;border:1px solid currentColor;{{ $step['state'] === 'current' ? 'font-weight:700' : ($step['state'] === 'pending' ? 'opacity:.55' : '') }}">
                            {{ $step['state'] === 'done' ? '✓ ' : '' }}{{ __($step['stage']) }}<span class="sr-only"> ({{ $step['state'] }})</span>
                        </li>
                    @endforeach
                </ol>
                <dl>
                    <dt style="font-weight:600">{{ __('Current status') }}</dt><dd>{{ __($application->status->label()) }}</dd>
                    <dt style="font-weight:600">{{ __('Destination') }}</dt><dd>{{ $application->destinationCountry->name }}</dd>
                    <dt style="font-weight:600">{{ __('Institution') }}</dt><dd>{{ $application->institution?->name ?? 'To be confirmed' }}</dd>
                    <dt style="font-weight:600">{{ __('Course') }}</dt><dd>{{ $application->course_name ?: 'To be confirmed' }}</dd>
                    <dt style="font-weight:600">{{ __('Intake') }}</dt><dd>{{ $application->intake ?: 'To be confirmed' }}</dd>
                    <dt style="font-weight:600">{{ __('Your consultant') }}</dt><dd>{{ $application->assignedConsultant ? 'Assigned' : 'Being assigned' }}</dd>
                </dl>
                <h2>{{ __('Timeline') }}</h2>
                <ol>
                    @foreach($timeline as $entry)
                        <li><strong>{{ __($entry['status']) }}</strong> — {{ $entry['date']->format('j M Y') }}@if($entry['note'])<br>{{ $entry['note'] }}@endif</li>
                    @endforeach
                </ol>
                <p class="egho-sample-note">{{ __('Admission decisions are made by the institution, and Student\'s Pass decisions by Singapore\'s Immigration & Checkpoints Authority (ICA). This page shows the progress recorded by your consultant.') }}</p>
            </section>

            <section class="egho-contact-card" aria-labelledby="documents-heading">
                <h2 id="documents-heading">{{ __('Documents') }}</h2>
                <p>{{ __('Your consultant will tell you which documents your application needs. Files are stored privately and are only visible to you and authorised Eagle Global Hub staff.') }}</p>
                <table style="width:100%;border-collapse:collapse">
                    <caption class="sr-only">{{ __('Document checklist') }}</caption>
                    <thead><tr><th scope="col" style="text-align:left">{{ __('Document') }}</th><th scope="col" style="text-align:left">{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @foreach($checklist as $item)
                            <tr><td>{{ __($item['label']) }}</td><td>{{ __($item['status']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>

                @if($documents->isNotEmpty())
                    <h3>{{ __('Uploaded files') }}</h3>
                    <ul>
                        @foreach($documents as $document)
                            <li>
                                <a href="{{ route('student.applications.documents.download', [$application, $document]) }}">{{ $document->original_name }}</a>
                                — {{ __($document->document_type->label()) }} — <strong>{{ __($document->statusLabel()) }}</strong>
                                @if($document->review_note)<br><small>{{ $document->review_note }}</small>@endif
                                @if($document->status !== \App\Models\StudentDocument::STATUS_VERIFIED)
                                    <form method="POST" action="{{ route('student.applications.documents.destroy', [$application, $document]) }}" style="display:inline" onsubmit="return confirm('Remove this document?')">@csrf @method('DELETE')<button type="submit" class="egho-btn">{{ __('Remove') }}</button></form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($application->acceptsUploads())
                    <form method="POST" action="{{ route('student.applications.documents.store', $application) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="egho-form-grid">
                            <label class="egho-field">
                                <span>{{ __('Document type') }}</span>
                                <select name="document_type" required>
                                    @foreach($documentTypes as $type)<option value="{{ $type->value }}" @selected(old('document_type') === $type->value)>{{ __($type->label()) }}</option>@endforeach
                                </select>
                            </label>
                            <label class="egho-field">
                                <span>File (PDF, JPG or PNG, max {{ $maxUploadMegabytes }} MB)</span>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
                            </label>
                        </div>
                        <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Upload document') }}</button></div>
                    </form>
                @else
                    <p>{{ __('This application is closed and no longer accepts documents.') }}</p>
                @endif
            </section>
        </div>

        <div class="egho-contact-grid">
            <section class="egho-contact-card" aria-labelledby="appointments-heading">
                <h2 id="appointments-heading">{{ __('Consultation appointments') }}</h2>
                @forelse($appointments as $appointment)
                    <p><strong>{{ $appointment->scheduled_at->format('j M Y, g:i A') }}</strong> — {{ __($appointmentModes[$appointment->mode] ?? $appointment->mode) }} — {{ __($appointment->statusLabel()) }}@if($appointment->status === 'confirmed' && $appointment->location_or_link)<br><small>{{ $appointment->location_or_link }}</small>@endif</p>
                @empty
                    <p>{{ __('No upcoming appointments.') }}</p>
                @endforelse
                @if(! $application->status->isClosed())
                    <form method="POST" action="{{ route('student.applications.appointments.store', $application) }}">
                        @csrf
                        <div class="egho-form-grid">
                            <label class="egho-field"><span>{{ __('Preferred date and time') }}</span><input type="datetime-local" name="scheduled_at" min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required></label>
                            <label class="egho-field"><span>{{ __('Meeting type') }}</span><select name="mode" required>@foreach($appointmentModes as $value => $label)<option value="{{ $value }}">{{ __($label) }}</option>@endforeach</select></label>
                            <label class="egho-field egho-field-wide"><span>{{ __('What would you like to discuss? (optional)') }}</span><textarea class="egho-textarea" name="student_note" maxlength="1000"></textarea></label>
                        </div>
                        <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Request appointment') }}</button></div>
                    </form>
                @endif
            </section>
            <section class="egho-contact-card" aria-labelledby="payments-heading">
                <h2 id="payments-heading">{{ __('Payments received') }}</h2>
                @forelse($payments as $payment)
                    <p><strong>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</strong> — {{ $payment->paid_on->format('j M Y') }} — {{ $payment->purpose }}<br><small>{{ __('Receipt reference') }}: {{ $payment->reference }}</small></p>
                @empty
                    <p>{{ __('No verified payments yet.') }}</p>
                @endforelse
                <p class="egho-sample-note">{{ __('Payments are made at our office or by bank / mobile banking transfer and are confirmed here after our accounts team verifies them. Online card payment is not available.') }}</p>
            </section>
        </div>

        <section class="egho-contact-card" aria-labelledby="messages-heading">
            <h2 id="messages-heading">{{ __('Messages with your consultant') }}</h2>
            @forelse($messages as $message)
                <article style="margin-bottom:12px"><strong>{{ $message['from'] }}</strong> <small>{{ $message['date']->format('j M Y, H:i') }}</small><p style="white-space:pre-line;margin:4px 0 0">{{ $message['body'] }}</p></article>
            @empty
                <p>{{ __('No messages yet.') }}</p>
            @endforelse
            <form method="POST" action="{{ route('student.applications.messages.store', $application) }}">
                @csrf
                <div class="egho-form-grid">
                    <label class="egho-field egho-field-wide"><span>{{ __('Your message') }}</span><textarea class="egho-textarea" name="body" maxlength="3000" required>{{ old('body') }}</textarea></label>
                </div>
                <div class="egho-form-foot"><button type="submit" class="egho-btn egho-btn-primary">{{ __('Send message') }}</button><small>{{ __('Please do not write passport or bank account numbers in messages.') }}</small></div>
            </form>
        </section>

        <p><a href="{{ route('student.applications.index') }}">{{ __('All my applications') }}</a> · <a href="{{ route('notifications.index') }}">{{ __('Notifications') }}</a></p>
    </div>
</main>
</x-site.portal>
@endsection
