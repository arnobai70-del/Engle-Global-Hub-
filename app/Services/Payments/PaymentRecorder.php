<?php

namespace App\Services\Payments;

use App\Models\Currency;
use App\Models\PaymentRecord;
use App\Models\User;
use App\Services\ActivityRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Records offline payments and their verification. No money moves through
 * this system: it is a ledger of payments staff received elsewhere.
 */
class PaymentRecorder
{
    public const DISK = 'payment_receipts';

    /** @var array<string, string> */
    private const RECEIPT_TYPES = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];

    public function __construct(private ActivityRecorder $activity) {}

    /**
     * @return list<string>
     */
    public static function currencies(): array
    {
        return collect(['BDT'])
            ->merge(Currency::query()->where('is_active', true)->orderBy('sort_order')->pluck('code'))
            ->map(fn (string $code): string => strtoupper($code))
            ->unique()->values()->all();
    }

    /**
     * @param  array{amount: string|float, currency: string, method: string, paid_on: string, payer_reference?: string|null, purpose: string}  $data
     *
     * @throws ValidationException when the same transaction was already recorded
     */
    public function record(Model $payable, array $data, ?UploadedFile $receipt, User $actor): PaymentRecord
    {
        $payerReference = isset($data['payer_reference']) ? trim((string) $data['payer_reference']) : null;
        $payerReference = $payerReference === '' ? null : $payerReference;

        if ($payerReference !== null && PaymentRecord::query()->where('method', $data['method'])->where('payer_reference', $payerReference)->exists()) {
            throw ValidationException::withMessages(['payer_reference' => 'A payment with this transaction reference has already been recorded.']);
        }

        [$path, $mime] = $receipt ? $this->storeReceipt($receipt) : [null, null];

        try {
            return DB::transaction(function () use ($payable, $data, $payerReference, $path, $mime, $actor): PaymentRecord {
                $payment = PaymentRecord::query()->create([
                    'reference' => $this->newReference(),
                    'payable_type' => $payable->getMorphClass(),
                    'payable_id' => $payable->getKey(),
                    'amount' => $data['amount'],
                    'currency' => strtoupper($data['currency']),
                    'method' => $data['method'],
                    'paid_on' => $data['paid_on'],
                    'payer_reference' => $payerReference,
                    'purpose' => $data['purpose'],
                    'receipt_path' => $path,
                    'receipt_mime' => $mime,
                    'recorded_by_user_id' => $actor->getKey(),
                ]);
                $payment->status = PaymentRecord::STATUS_PENDING;

                $this->activity->record($actor, 'payment.recorded', $payment, [
                    'reference' => $payment->reference, 'amount' => (string) $payment->amount, 'currency' => $payment->currency,
                ]);

                return $payment;
            });
        } catch (Throwable $exception) {
            if ($path !== null) {
                Storage::disk(self::DISK)->delete($path);
            }

            throw $exception;
        }
    }

    /**
     * @throws ValidationException when staff try to verify their own entry or reject without a reason
     */
    public function verify(PaymentRecord $payment, string $status, User $actor, ?string $note): void
    {
        if ((int) $payment->recorded_by_user_id === (int) $actor->getKey()) {
            throw ValidationException::withMessages(['status' => 'A payment must be verified by someone other than the person who recorded it.']);
        }
        if ($payment->status !== PaymentRecord::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'This payment has already been reviewed.']);
        }
        if ($status === PaymentRecord::STATUS_REJECTED && blank($note)) {
            throw ValidationException::withMessages(['verification_note' => 'Give a reason for rejecting this payment.']);
        }

        DB::transaction(function () use ($payment, $status, $actor, $note): void {
            $payment->forceFill([
                'status' => $status,
                'verified_by_user_id' => $actor->getKey(),
                'verified_at' => now(),
                'verification_note' => $note,
            ])->save();
            $this->activity->record($actor, 'payment.'.$status, $payment, ['reference' => $payment->reference]);
        });
    }

    public function downloadReceipt(PaymentRecord $payment): StreamedResponse
    {
        abort_if($payment->receipt_path === null, 404);

        return Storage::disk(self::DISK)->download(
            $payment->receipt_path,
            'receipt-'.$payment->reference.'.'.(self::RECEIPT_TYPES[$payment->receipt_mime] ?? 'bin'),
            ['Content-Type' => (string) $payment->receipt_mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'],
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeReceipt(UploadedFile $receipt): array
    {
        $mime = (string) $receipt->getMimeType();
        $extension = self::RECEIPT_TYPES[$mime] ?? null;
        if ($extension === null) {
            throw ValidationException::withMessages(['receipt' => 'Receipts must be PDF, JPG or PNG files.']);
        }

        return [Storage::disk(self::DISK)->putFileAs(now()->format('Y/m'), $receipt, Str::uuid()->toString().'.'.$extension), $mime];
    }

    private function newReference(): string
    {
        do {
            $reference = 'EGH-PAY-'.Str::upper(Str::random(10));
        } while (PaymentRecord::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
