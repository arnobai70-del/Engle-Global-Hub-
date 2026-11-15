<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A study-abroad lead captured from the public consultation form.
 */
class StudentEnquiry extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_CONSULTATION_SCHEDULED = 'consultation_scheduled';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_NOT_QUALIFIED = 'not_qualified';

    public const STATUS_CLOSED = 'closed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_NEW, self::STATUS_CONTACTED, self::STATUS_CONSULTATION_SCHEDULED,
        self::STATUS_CONVERTED, self::STATUS_NOT_QUALIFIED, self::STATUS_CLOSED,
    ];

    /** @var array<string, string> */
    public const LEAD_SOURCES = [
        'organic_search' => 'Organic Search',
        'google_ads' => 'Google Ads',
        'facebook_instagram' => 'Facebook / Instagram',
        'whatsapp' => 'WhatsApp',
        'referral' => 'Referral',
        'direct' => 'Direct Website Visit',
    ];

    /** @var array<string, string> */
    public const STUDY_LEVELS = [
        'diploma' => 'Diploma',
        'foundation' => 'Foundation',
        'bachelor' => "Bachelor's degree",
        'master' => "Master's degree",
        'other' => 'Other / not sure yet',
    ];

    /** @var list<string> */
    protected $fillable = [
        'reference', 'full_name', 'email', 'phone', 'destination_country_id',
        'highest_qualification', 'study_level', 'preferred_intake', 'wants_consultation',
        'message', 'lead_source', 'consented_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'destination_country_id' => 'integer',
            'wants_consultation' => 'boolean',
            'consented_at' => 'datetime',
            'follow_up_at' => 'datetime',
        ];
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StudentApplication::class);
    }

    public function referredByAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referred_by_agent_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderBy('scheduled_at');
    }
}
