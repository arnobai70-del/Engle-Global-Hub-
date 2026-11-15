<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A manually recorded offline payment (cash, bank transfer, mobile banking).
 * It records money staff say was received; no gateway is involved and nothing
 * is charged by this system.
 */
class PaymentRecord extends Model
{
    public const STATUS_PENDING = 'pending_verification';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    /** @var array<string, string> */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pending verification',
        self::STATUS_VERIFIED => 'Verified',
        self::STATUS_REJECTED => 'Rejected',
    ];

    /** @var array<string, string> */
    public const METHODS = [
        'cash' => 'Cash (office)',
        'bank_transfer' => 'Bank transfer / deposit',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'card_pos' => 'Card (office POS)',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ];

    /** @var array<string, class-string<Model>> */
    public const PAYABLES = [
        'student_application' => StudentApplication::class,
        'service_enquiry' => ServiceEnquiry::class,
        'flight_enquiry' => FlightEnquiry::class,
    ];

    /** @var list<string> */
    protected $fillable = [
        'reference', 'payable_type', 'payable_id', 'amount', 'currency', 'method',
        'paid_on', 'payer_reference', 'purpose', 'receipt_path', 'receipt_mime', 'recorded_by_user_id',
    ];

    /** @var list<string> */
    protected $hidden = ['receipt_path'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_on' => 'date', 'verified_at' => 'datetime'];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function payableLabel(): string
    {
        $payable = $this->payable;

        return match (true) {
            $payable instanceof StudentApplication => 'Student application '.$payable->reference,
            $payable instanceof ServiceEnquiry => 'Service enquiry '.$payable->reference,
            $payable instanceof FlightEnquiry => 'Flight enquiry '.$payable->reference,
            default => 'Unlinked',
        };
    }
}
