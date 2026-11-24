@extends('layouts.admin')
@section('title', 'Legal Documents')
@section('content')
<x-admin.page-header title="Legal Documents" description="Originals are stored privately. Only the owner can verify, approve, and publish company credentials." icon="L" eyebrow="Website CMS" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="egh-card">
    <h2>Upload original government document (private)</h2>
    <p>This file is never public. Do not upload the original into the Media Library.</p>
    <form method="POST" action="{{ route('admin.legal-documents.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="stu-form-grid">
            <label>Document title<input name="title" required maxlength="180" value="{{ old('title') }}"></label>
            <label>Type<select name="document_type" required><option value="">Select type</option>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(old('document_type') === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Issuing authority<input name="issuer" required maxlength="180" value="{{ old('issuer') }}"></label>
            <label>Registration / licence number<input name="reference_number" maxlength="120" value="{{ old('reference_number') }}"></label>
            <label>Issue date<input type="date" name="issued_at" value="{{ old('issued_at') }}"></label>
            <label>Expiry date (optional)<input type="date" name="expires_at" value="{{ old('expires_at') }}"></label>
            <label class="stu-full">Original PDF, max 8 MB<input type="file" name="original_pdf" accept="application/pdf,.pdf" required></label>
        </div>
        <div class="admin-form-actions"><button class="egh-button" type="submit">Save as private draft</button></div>
    </form>
</div>

<div class="egh-card admin-table-card">
    <h2>Documents and approvals</h2>
    <table class="stu-table">
        <thead><tr><th>Document</th><th>Verification</th><th>Publication</th><th>Expiry</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($documents as $document)
            <tr>
                <td><strong>{{ $document->title }}</strong><p>{{ $types[$document->document_type] ?? $document->document_type }} · {{ $document->issuer }}</p><p>Reference: {{ $document->reference_number ?: '—' }}</p></td>
                <td>{{ ucfirst($document->verification_status) }}@if($document->approved_at)<p>Approved {{ $document->approved_at->format('Y-m-d') }}</p>@endif</td>
                <td>{{ ucfirst($document->publication_status) }}</td>
                <td>{{ $document->expires_at?->format('Y-m-d') ?? 'Not stated' }}@if($document->expires_at && $document->expires_at->isBefore(today()))<strong>Expired</strong>@elseif($document->expires_at && $document->expires_at->lte(today()->addDays(30)))<strong>Renewal due soon</strong>@endif</td>
                <td>
                    <p><a href="{{ route('admin.legal-documents.original', $document) }}">Download original (private)</a></p>
                    @if($document->public_copy_path)<p><a href="{{ route('admin.legal-documents.redacted', $document) }}">Review redacted copy (private)</a></p>@endif
                    <form method="POST" action="{{ route('admin.legal-documents.update', $document) }}" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        <label>Title<input name="title" required value="{{ $document->title }}"></label>
                        <label>Type<select name="document_type">@foreach($types as $key => $label)<option value="{{ $key }}" @selected($document->document_type === $key)>{{ $label }}</option>@endforeach</select></label>
                        <label>Issuer<input name="issuer" required value="{{ $document->issuer }}"></label>
                        <label>Reference<input name="reference_number" value="{{ $document->reference_number }}"></label>
                        <label>Issue date<input name="issued_at" type="date" value="{{ $document->issued_at?->format('Y-m-d') }}"></label>
                        <label>Expiry date<input name="expires_at" type="date" value="{{ $document->expires_at?->format('Y-m-d') }}"></label>
                        <label>Optional separately redacted PDF, max 8 MB<input type="file" name="redacted_pdf" accept="application/pdf,.pdf"></label>
                        @if($document->public_copy_path)<label><input type="checkbox" name="remove_redacted_copy" value="1"> Remove public copy</label>@endif
                        <button class="egh-button secondary" type="submit">Save revisions (resets approval)</button>
                    </form>
                    @role('super-admin')
                    @if(!$document->approved_at)
                        <form method="POST" action="{{ route('admin.legal-documents.approve', $document) }}">
                            @csrf
                            <label><input type="checkbox" name="confirm_official_source" value="1" required> I verified the issuing authority and the details.</label>
                            @if($document->public_copy_path)
                                <label><input type="checkbox" name="confirm_redacted_copy" value="1" required> I inspected the redacted PDF, including embedded text, metadata, QR codes and signatures; it reveals no private information.</label>
                            @endif
                            <button class="egh-button" type="submit">Owner approve (does not publish)</button>
                        </form>
                    @endif
                    @if($document->publication_status === 'published')
                        <form method="POST" action="{{ route('admin.legal-documents.unpublish', $document) }}">@csrf<button class="egh-button danger" type="submit">Unpublish</button></form>
                    @elseif($document->approved_at)
                        <form method="POST" action="{{ route('admin.legal-documents.publish', $document) }}">@csrf<button class="egh-button" type="submit">Publish approved metadata</button></form>
                    @endif
                    @endrole
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No legal documents uploaded. The public legal-document list will be empty.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $documents->links() }}
</div>
@endsection
