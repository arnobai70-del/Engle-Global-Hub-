@extends('layouts.admin')
@section('title', 'Payment '.$payment->reference)
@section('content')
<x-admin.page-header :title="$payment->reference" :description="$payment->statusLabel()" icon="P" eyebrow="Finance"><a class="egh-button secondary" href="{{ route('admin.payments.index') }}">Back</a></x-admin.page-header>
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="egh-card"><dl class="stu-show">
<dt>For</dt><dd>{{ $payment->payableLabel() }}</dd>
<dt>Amount</dt><dd>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</dd>
<dt>Method</dt><dd>{{ \App\Models\PaymentRecord::METHODS[$payment->method] ?? $payment->method }}</dd>
<dt>Paid on</dt><dd>{{ $payment->paid_on->format('j M Y') }}</dd>
<dt>Transaction reference</dt><dd>{{ $payment->payer_reference ?? '—' }}</dd>
<dt>Purpose</dt><dd>{{ $payment->purpose }}</dd>
<dt>Receipt</dt><dd>@if($payment->receipt_mime)<a class="stu-link" href="{{ route('admin.payments.receipt', $payment) }}">Download receipt</a>@else Not attached @endif</dd>
<dt>Recorded by</dt><dd>{{ $payment->recordedBy?->name ?? '—' }}, {{ $payment->created_at->format('j M Y H:i') }}</dd>
@if($payment->verified_at)<dt>Reviewed by</dt><dd>{{ $payment->verifiedBy?->name ?? '—' }}, {{ $payment->verified_at->format('j M Y H:i') }}@if($payment->verification_note) — {{ $payment->verification_note }}@endif</dd>@endif
</dl></div>
@can('payments.verify')@if($payment->status === \App\Models\PaymentRecord::STATUS_PENDING)
<div class="egh-card"><h2>Verify payment</h2>
<p>Check the amount against the bank, mobile-banking or cash records before verifying. You cannot verify a payment you recorded.</p>
<form method="POST" action="{{ route('admin.payments.verify', $payment) }}">@csrf @method('PATCH')
<div class="stu-form-grid"><label>Decision<select name="status"><option value="verified">Verified — money received</option><option value="rejected">Rejected</option></select></label><label class="stu-full">Note<textarea name="verification_note" maxlength="1000"></textarea></label></div>
<div class="admin-form-actions"><button class="egh-button">Save decision</button></div></form></div>
@endif @endcan
@endsection
