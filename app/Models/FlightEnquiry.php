<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class FlightEnquiry extends Model
{
    protected $fillable = [
        'user_id', 'reference', 'origin', 'destination', 'departure_date',
        'return_date', 'trip_type', 'adults', 'children', 'infants',
        'cabin_class', 'contact_name', 'contact_email', 'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'quoted_total_bdt' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(PaymentRecord::class, 'payable')->latest('paid_on');
    }
}
