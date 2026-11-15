@extends('layouts.admin')
@section('title', 'Record Payment')
@section('content')
<x-admin.page-header title="Record Payment" description="Record money already received offline. A second staff member must verify it." icon="P" eyebrow="Finance" />
<div class="egh-card">
@if($errors->any())<div class="egh-alert is-error" role="alert"><ul class="admin-error-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<p>For: <strong>{{ (new \App\Models\PaymentRecord)->setRelation('payable', $payable)->payableLabel() }}</strong></p>
<form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data">@csrf
<input type="hidden" name="payable_type" value="{{ $payableType }}"><input type="hidden" name="payable_id" value="{{ $payable->getKey() }}">
<div class="stu-form-grid">
<label>Amount<input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required></label>
<label>Currency<select name="currency" required>@foreach($currencies as $code)<option value="{{ $code }}" @selected(old('currency', 'BDT') === $code)>{{ $code }}</option>@endforeach</select></label>
<label>Method<select name="method" required>@foreach(\App\Models\PaymentRecord::METHODS as $value => $label)<option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>@endforeach</select></label>
<label>Payment date<input type="date" name="paid_on" max="{{ now()->toDateString() }}" value="{{ old('paid_on', now()->toDateString()) }}" required></label>
<label>Transaction / receipt number<input name="payer_reference" maxlength="100" value="{{ old('payer_reference') }}"></label>
<label>Purpose<input name="purpose" maxlength="200" value="{{ old('purpose') }}" placeholder="e.g. Consultancy service fee" required></label>
<label>Receipt (PDF, JPG or PNG; max 5 MB)<input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png"></label>
</div>
<div class="admin-form-actions"><button class="egh-button">Record payment</button></div>
</form></div>
@endsection
