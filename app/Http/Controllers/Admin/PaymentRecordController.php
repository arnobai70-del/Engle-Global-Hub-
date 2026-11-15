<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRecord;
use App\Models\StudentApplication;
use App\Services\Payments\PaymentRecorder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentRecordController extends Controller
{
    public function __construct(private PaymentRecorder $payments) {}

    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->query('status'), PaymentRecord::STATUSES) ? (string) $request->query('status') : '';

        return view('admin.payments.index', [
            'payments' => $this->visibleTo($request, PaymentRecord::query())
                ->with(['payable', 'recordedBy:id,name', 'verifiedBy:id,name'])
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->latest('id')->paginate(25)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        $payable = $this->payable($request, (string) $request->query('payable_type'), (int) $request->query('payable_id'));

        return view('admin.payments.create', [
            'payable' => $payable,
            'payableType' => (string) $request->query('payable_type'),
            'currencies' => PaymentRecorder::currencies(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payable_type' => ['required', Rule::in(array_keys(PaymentRecord::PAYABLES))],
            'payable_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'currency' => ['required', Rule::in(PaymentRecorder::currencies())],
            'method' => ['required', Rule::in(array_keys(PaymentRecord::METHODS))],
            'paid_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'payer_reference' => ['nullable', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:200'],
            'receipt' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(5120)],
        ]);

        $payable = $this->payable($request, $data['payable_type'], (int) $data['payable_id']);
        $payment = $this->payments->record($payable, $data, $request->file('receipt'), $request->user());

        return redirect()->route('admin.payments.show', $payment)->with('status', 'Payment recorded and waiting for verification.');
    }

    public function show(Request $request, PaymentRecord $payment): View
    {
        $this->authorizeView($request, $payment);

        return view('admin.payments.show', ['payment' => $payment->load(['payable', 'recordedBy:id,name', 'verifiedBy:id,name'])]);
    }

    public function receipt(Request $request, PaymentRecord $payment): StreamedResponse
    {
        $this->authorizeView($request, $payment);

        return $this->payments->downloadReceipt($payment);
    }

    public function verify(Request $request, PaymentRecord $payment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in([PaymentRecord::STATUS_VERIFIED, PaymentRecord::STATUS_REJECTED])],
            'verification_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->payments->verify($payment, $data['status'], $request->user(), $data['verification_note'] ?? null);

        return redirect()->route('admin.payments.show', $payment)->with('status', 'Payment '.$data['status'].'.');
    }

    private function payable(Request $request, string $type, int $id): Model
    {
        $class = PaymentRecord::PAYABLES[$type] ?? null;
        abort_if($class === null, 404);
        $payable = $class::query()->findOrFail($id);

        if ($payable instanceof StudentApplication) {
            Gate::authorize('view', $payable);
        } else {
            abort_unless($request->user()->can('student-applications.view-all'), 403);
        }

        return $payable;
    }

    private function authorizeView(Request $request, PaymentRecord $payment): void
    {
        abort_unless($this->visibleTo($request, PaymentRecord::query())->whereKey($payment->getKey())->exists(), 403);
    }

    /**
     * Staff who cannot see every application only see payments for the
     * applications assigned to them.
     *
     * @param  Builder<PaymentRecord>  $query
     * @return Builder<PaymentRecord>
     */
    private function visibleTo(Request $request, Builder $query): Builder
    {
        $user = $request->user();
        if ($user->can('student-applications.view-all')) {
            return $query;
        }

        return $query->where('payable_type', (new StudentApplication)->getMorphClass())
            ->whereIn('payable_id', StudentApplication::query()->where('assigned_consultant_id', $user->getKey())->select('id'));
    }
}
