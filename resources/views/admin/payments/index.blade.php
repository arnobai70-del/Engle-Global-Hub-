@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
<x-admin.page-header title="Payment Records" description="Offline payments recorded by staff. No online payment gateway is connected; nothing is charged here." icon="P" eyebrow="Finance" />
@if(session('status'))<div class="egh-alert" role="status">{{ session('status') }}</div>@endif
<div class="egh-card stu-filter"><a class="egh-button {{ $status === '' ? '' : 'secondary' }}" href="{{ route('admin.payments.index') }}">All</a>@foreach(\App\Models\PaymentRecord::STATUSES as $value => $label) <a class="egh-button {{ $status === $value ? '' : 'secondary' }}" href="{{ route('admin.payments.index', ['status' => $value]) }}">{{ $label }}</a>@endforeach</div>
<div class="egh-card admin-table-card"><table class="stu-table"><thead><tr><th>Reference</th><th>For</th><th>Amount</th><th>Method</th><th>Paid on</th><th>Status</th><th>Recorded by</th><th></th></tr></thead><tbody>
@forelse($payments as $payment)<tr><td>{{ $payment->reference }}</td><td>{{ $payment->payableLabel() }}</td><td>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td><td>{{ \App\Models\PaymentRecord::METHODS[$payment->method] ?? $payment->method }}</td><td>{{ $payment->paid_on->format('Y-m-d') }}</td><td>{{ $payment->statusLabel() }}</td><td>{{ $payment->recordedBy?->name ?? '—' }}</td><td><a class="stu-link" href="{{ route('admin.payments.show', $payment) }}">Open</a></td></tr>@empty<tr><td colspan="8">No payments recorded.</td></tr>@endforelse
</tbody></table></div>@if($payments->hasPages())<div class="admin-pagination">{{ $payments->links() }}</div>@endif
@endsection
