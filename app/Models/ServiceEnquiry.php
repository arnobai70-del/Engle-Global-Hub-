<?php

namespace App\Models;

use App\Enums\BusinessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A manual enquiry for a travel or visa service that has no live booking
 * provider. Staff respond by phone or email; nothing is booked automatically.
 */
class ServiceEnquiry extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_QUOTATION_SENT = 'quotation_sent';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CLOSED = 'closed';

    /** @var array<string, string> */
    public const STATUSES = [
        self::STATUS_NEW => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_QUOTATION_SENT => 'Quotation sent',
        self::STATUS_CONFIRMED => 'Confirmed by staff',
        self::STATUS_CLOSED => 'Closed',
    ];

    /**
     * Services handled by this enquiry form. Student visas and air tickets
     * have their own dedicated workflows.
     *
     * @var list<string>
     */
    public const SERVICES = [
        'tourist_visa', 'business_visa', 'work_visa', 'hotel_booking',
        'tour_package', 'train', 'bus', 'car', 'bike',
        'export_import', 'civil_construction', 'foreigner_support',
    ];

    /**
     * Optional sub-types per service, chosen on the enquiry form.
     *
     * @var array<string, array<string, string>>
     */
    public const CATEGORIES = [
        'tour_package' => ['holiday_package' => 'Holiday package', 'group_tour' => 'Group tour', 'private_tour' => 'Private tour'],
        'export_import' => ['import' => 'Import', 'export' => 'Export'],
        'civil_construction' => ['civil_construction' => 'Civil construction', 'land_related' => 'Land-related work', 'other' => 'Other'],
        'foreigner_support' => [
            'travel_assistance' => 'Travel assistance', 'accommodation' => 'Accommodation', 'transport' => 'Local transport',
            'visa_information' => 'Visa information', 'student_support' => 'Student support', 'other' => 'Other',
        ],
    ];

    /** @var list<string> */
    protected $fillable = [
        'reference', 'service', 'category', 'user_id', 'destination_country_id', 'route_or_destination',
        'travel_date', 'return_date', 'travellers', 'full_name', 'email', 'phone', 'message', 'consented_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'service' => BusinessService::class,
            'travel_date' => 'date',
            'return_date' => 'date',
            'consented_at' => 'datetime',
            'travellers' => 'integer',
        ];
    }

    /**
     * Resolves a URL slug such as "tourist-visa" to a service handled here.
     */
    public static function serviceFromSlug(string $slug): BusinessService
    {
        $value = str_replace('-', '_', $slug);
        abort_unless(in_array($value, self::SERVICES, true), 404);

        return BusinessService::from($value);
    }

    public static function slugFor(BusinessService $service): string
    {
        return str_replace('_', '-', $service->value);
    }

    public function categoryLabel(): ?string
    {
        return $this->category === null ? null : (self::CATEGORIES[$this->service->value][$this->category] ?? $this->category);
    }

    public function referredByAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referred_by_agent_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    /**
     * Explore Bangladesh destinations a "Plan my trip" enquiry asks about.
     *
     * @return BelongsToMany<Destination, $this>
     */
    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(PaymentRecord::class, 'payable')->latest('paid_on');
    }
}
