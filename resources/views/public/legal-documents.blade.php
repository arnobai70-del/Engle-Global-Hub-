@extends('layouts.site')
@section('title', __('Company Credentials'))
@section('meta_description', __('Publicly verified company registration and trade licence information.'))
@section('body_class', 'sg-body-v2')
@section('content')
<main class="v2-about">
    <section class="v2-section">
        <div class="v2-shell">
            <h1 class="v2-h2">{{ __('Company Credentials') }}</h1>
            <p>{{ __('These records describe company registration or specific licences only; they do not guarantee any visa, admission or booking outcome.') }}</p>
            <div class="v2-related">
                @forelse($documents as $document)
                    <article class="v2-card">
                        <h2>{{ $document->title }}</h2>
                        <p>{{ $document->issuer }}</p>
                        @if($document->reference_number)<p>{{ __('Reference') }}: {{ $document->reference_number }}</p>@endif
                        @if($document->issued_at)<p>{{ __('Issued') }}: {{ $document->issued_at->format('Y-m-d') }}</p>@endif
                        @if($document->expires_at)<p>{{ __('Valid until') }}: {{ $document->expires_at->format('Y-m-d') }}</p>@endif
                        @if($document->public_copy_path && $document->redaction_confirmed)
                            <p><a href="{{ route('legal-documents.download', $document) }}">{{ __('Download approved redacted copy') }}</a></p>
                        @endif
                    </article>
                @empty
                    <p>{{ __('Company documents are being reviewed for public release.') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</main>
@endsection
